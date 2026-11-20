<?php

declare(strict_types=1);

namespace a9f\FractorXml\Tests\Printer;

use a9f\Fractor\ValueObject\Indent;
use a9f\FractorXml\Printer\SourceMap;
use a9f\FractorXml\Printer\XmlPrinter;
use a9f\FractorXml\ValueObjectFactory\DomDocumentFactory;
use Nette\Utils\FileSystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

final class XmlPrinterTest extends TestCase
{
    #[DataProvider('provideFixtures')]
    public function testKeepsTheLayoutOfTheAuthorAndOnlyChangesTheIndentation(string $fixture, Indent $indent): void
    {
        [$input, $expected] = self::readFixture($fixture);

        self::assertSame($expected, self::print($input, $indent));
    }

    #[DataProvider('provideFixtures')]
    public function testPrintingAPrintedFileChangesNothing(string $fixture, Indent $indent): void
    {
        [$input] = self::readFixture($fixture);
        $printed = self::print($input, $indent);

        self::assertSame($printed, self::print($printed, $indent));
    }

    /**
     * @return \Iterator<string, array{string, Indent}>
     */
    public static function provideFixtures(): \Iterator
    {
        $spaces = Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE);
        $tabs = Indent::fromSizeAndStyle(1, Indent::STYLE_TAB);

        yield 'attributes on several lines' => ['attributes-on-several-lines.xml.fixture', $spaces];
        yield 'attributes on several lines with tabs' => ['attributes-on-several-lines-tabs.xml.fixture', $tabs];
        yield 'attribute groups' => ['attribute-groups.xml.fixture', $spaces];
        yield 'xliff attribute order and declaration' => ['xliff-order-and-declaration.xml.fixture', $spaces];
        yield 'blank lines and comments' => ['blank-lines-and-comments.xml.fixture', $spaces];
        yield 'empty elements without declaration' => ['empty-elements-without-declaration.xml.fixture', $spaces];
        yield 'entities and quotes' => ['entities-and-quotes.xml.fixture', $spaces];
        yield 'text content' => ['text-content.xml.fixture', $spaces];
        yield 'cdata, processing instructions and doctype' => ['cdata-pi-doctype.xml.fixture', $spaces];
        yield 'byte order mark' => ['byte-order-mark.xml.fixture', $spaces];
        yield 'byte order mark and closing bracket' => [
            'byte-order-mark-closing-bracket.xml.fixture',
            Indent::fromSizeAndStyle(2, Indent::STYLE_SPACE),
        ];
        yield 'adjacent cdata sections' => ['adjacent-cdata.xml.fixture', $spaces];
        yield 'processing instructions' => ['processing-instructions.xml.fixture', $spaces];
        yield 'attribute types of a dtd' => ['dtd-attribute-types.xml.fixture', $spaces];
        yield 'elements inside text' => ['elements-inside-text.xml.fixture', $spaces];
        yield 'whitespace-only content' => ['whitespace-only-content.xml.fixture', $spaces];
        yield 'blank line after the declaration' => ['blank-line-after-declaration.xml.fixture', $spaces];
        yield 'character reference between elements' => ['character-reference-between-elements.xml.fixture', $spaces];
    }

    /**
     * @param \Closure(\DOMDocument): (\DOMDocument|null) $change
     */
    #[DataProvider('provideChanges')]
    public function testPrintsWhatARuleChangedInAFixedForm(string $input, \Closure $change, string $expected): void
    {
        $document = (new DomDocumentFactory())->createFromXml($input);
        $sourceMap = SourceMap::create($input, $document);
        $printedDocument = $change($document) ?? $document;

        $printer = new XmlPrinter();
        $printed = $printer->print($printedDocument, Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE), $sourceMap);

        self::assertSame($expected, $printed);
    }

    /**
     * @return \Iterator<string, array{string, \Closure(\DOMDocument): (\DOMDocument|null), string}>
     */
    public static function provideChanges(): \Iterator
    {
        yield 'changed attribute value stays in place' => [
            <<<'XML_DOCUMENT'
            <guides
                xmlns="https://www.phpdoc.org/guides"
                input-format="rst"
            >
                <project title='old' copyright="The contributors"/>
            </guides>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'guides')->setAttribute('input-format', 'md');
                self::element($document, 'project')->setAttribute('title', 'new & "improved"');
                return null;
            },
            <<<'XML_DOCUMENT'
            <guides
                xmlns="https://www.phpdoc.org/guides"
                input-format="md"
            >
                <project title='new &amp; "improved"' copyright="The contributors"/>
            </guides>

            XML_DOCUMENT
            ,
        ];

        yield 'value set to the same value keeps how it was written' => [
            <<<'XML_DOCUMENT'
            <root>
                <item title='It&apos;s'/>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'item')->setAttribute('title', "It's");
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <item title='It&apos;s'/>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'new attribute on a one-line tag goes to the end of the line' => [
            <<<'XML_DOCUMENT'
            <xliff>
                <file source-language="en" original="messages">
                    <body/>
                </file>
            </xliff>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'file')->setAttribute('target-language', 'de');
                return null;
            },
            <<<'XML_DOCUMENT'
            <xliff>
                <file source-language="en" original="messages" target-language="de">
                    <body/>
                </file>
            </xliff>

            XML_DOCUMENT
            ,
        ];

        yield 'new attribute on a multi-line tag gets its own line' => [
            <<<'XML_DOCUMENT'
            <xliff>
                <file
                    source-language="en"
                    original="messages"
                >
                    <body/>
                </file>
            </xliff>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'file')->setAttribute('target-language', 'de');
                return null;
            },
            <<<'XML_DOCUMENT'
            <xliff>
                <file
                    source-language="en"
                    original="messages"
                    target-language="de"
                >
                    <body/>
                </file>
            </xliff>

            XML_DOCUMENT
            ,
        ];

        yield 'new attribute joins a last line with several attributes' => [
            <<<'XML_DOCUMENT'
            <xliff
                xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0">
                <file id="messages"/>
            </xliff>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'xliff')->setAttribute('srcLang', 'en');
                return null;
            },
            <<<'XML_DOCUMENT'
            <xliff
                xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en">
                <file id="messages"/>
            </xliff>

            XML_DOCUMENT
            ,
        ];

        yield 'new attribute replacing all attributes keeps their lines' => [
            <<<'XML_DOCUMENT'
            <root>
                <file
                    a="1"
                >
                    <x/>
                </file>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                $file = self::element($document, 'file');
                $file->removeAttribute('a');
                $file->setAttribute('b', '2');
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <file
                    b="2"
                >
                    <x/>
                </file>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'changed spaces of an attribute are printed in a file with a doctype' => [
            <<<'XML_DOCUMENT'
            <!DOCTYPE root>
            <root>
                <a v=" x  y "/>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'a')->setAttribute('v', 'x y');
                return null;
            },
            <<<'XML_DOCUMENT'
            <!DOCTYPE root>
            <root>
                <a v="x y"/>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'removed attribute takes its line with it' => [
            <<<'XML_DOCUMENT'
            <xliff>
                <file
                    source-language="en"
                    original="messages"
                >
                    <body/>
                </file>
            </xliff>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'file')->removeAttribute('original');
                return null;
            },
            <<<'XML_DOCUMENT'
            <xliff>
                <file
                    source-language="en"
                >
                    <body/>
                </file>
            </xliff>

            XML_DOCUMENT
            ,
        ];

        yield 'new namespace declaration is added like an attribute' => [
            <<<'XML_DOCUMENT'
            <phpunit colors="true">
                <testsuites/>
            </phpunit>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                $root = self::element($document, 'phpunit');
                $root->setAttributeNS(
                    'http://www.w3.org/2000/xmlns/',
                    'xmlns:xsi',
                    'http://www.w3.org/2001/XMLSchema-instance'
                );
                $root->setAttributeNS(
                    'http://www.w3.org/2001/XMLSchema-instance',
                    'xsi:noNamespaceSchemaLocation',
                    'phpunit.xsd'
                );
                return null;
            },
            <<<'XML_DOCUMENT'
            <phpunit colors="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="phpunit.xsd">
                <testsuites/>
            </phpunit>

            XML_DOCUMENT
            ,
        ];

        yield 'new element gets its own line' => [
            <<<'XML_DOCUMENT'
            <T3DataStructure>
                <config>
                    <type>input</type>
                    <eval>null,trim</eval>
                </config>
            </T3DataStructure>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                $eval = self::element($document, 'eval');
                $eval->nodeValue = '';
                $eval->appendChild($document->createTextNode('trim'));
                self::element($document, 'config')->appendChild($document->createElement('nullable', 'true'));
                return null;
            },
            <<<'XML_DOCUMENT'
            <T3DataStructure>
                <config>
                    <type>input</type>
                    <eval>trim</eval>
                    <nullable>true</nullable>
                </config>
            </T3DataStructure>

            XML_DOCUMENT
            ,
        ];

        yield 'new element in the default namespace gets no declaration' => [
            <<<'XML_DOCUMENT'
            <xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
                <file/>
            </xliff>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'file')->appendChild(
                    $document->createElementNS('urn:oasis:names:tc:xliff:document:1.2', 'body')
                );
                return null;
            },
            <<<'XML_DOCUMENT'
            <xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
                <file>
                    <body/>
                </file>
            </xliff>

            XML_DOCUMENT
            ,
        ];

        yield 'removed element takes its line with it' => [
            <<<'XML_DOCUMENT'
            <root>
                <a/>

                <b/>
                <c/>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                $c = self::element($document, 'c');
                $c->parentNode?->removeChild($c);
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <a/>

                <b/>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'element without children left is self-closing' => [
            <<<'XML_DOCUMENT'
            <root>
                <config>
                    <type>input</type>
                </config>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                $type = self::element($document, 'type');
                $type->parentNode?->removeChild($type);
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <config/>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'changed text is escaped' => [
            <<<'XML_DOCUMENT'
            <root>
                <label>old</label>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'label')->textContent = 'a & b -> c < d';
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <label>a &amp; b -> c &lt; d</label>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'changed text keeps characters outside of ASCII' => [
            <<<'XML_DOCUMENT'
            <root>
                <label>old</label>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'label')->textContent = 'Grüße – 5 €';
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <label>Grüße – 5 €</label>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'text set on an element that held elements is the only content' => [
            <<<'XML_DOCUMENT'
            <root>
                <a>
                    <b/>
                </a>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'a')->nodeValue = 'new';
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <a>new</a>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'removed element in text content takes no whitespace along' => [
            <<<'XML_DOCUMENT'
            <root>
                <p><b>x</b> <i>y</i> tail</p>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                $i = self::element($document, 'i');
                $i->parentNode?->removeChild($i);
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <p><b>x</b> tail</p>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'new element inside text drops whitespace between elements' => [
            <<<'XML_DOCUMENT'
            <root>
                <p>Text <span>
                    </span></p>
            </root>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                self::element($document, 'span')->appendChild($document->createElement('b'));
                return null;
            },
            <<<'XML_DOCUMENT'
            <root>
                <p>Text <span><b/></span></p>
            </root>

            XML_DOCUMENT
            ,
        ];

        yield 'copied nodes are printed in the fixed form' => [
            <<<'XML_DOCUMENT'
            <T3DataStructure>
                <settings.a>
                    <TCEforms>
                        <label>A</label>
                        <config>
                            <type>input</type>
                        </config>
                    </TCEforms>
                </settings.a>
            </T3DataStructure>

            XML_DOCUMENT
            ,
            static function (\DOMDocument $document): null {
                $tceForms = self::element($document, 'TCEforms');
                $parent = $tceForms->parentNode;
                self::assertInstanceOf(\DOMNode::class, $parent);
                foreach (iterator_to_array($tceForms->childNodes) as $child) {
                    $parent->insertBefore($child->cloneNode(true), $tceForms);
                }
                $parent->removeChild($tceForms);
                return null;
            },
            <<<'XML_DOCUMENT'
            <T3DataStructure>
                <settings.a>
                    <label>A</label>
                    <config>
                        <type>input</type>
                    </config>
                </settings.a>
            </T3DataStructure>

            XML_DOCUMENT
            ,
        ];

        yield 'new document is printed in the fixed form' => [
            <<<'XML_DOCUMENT'
            <?xml version="1.0" encoding="UTF-8" ?>
            <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2"/>

            XML_DOCUMENT
            ,
            static function (): \DOMDocument {
                $namespace = 'urn:oasis:names:tc:xliff:document:2.0';
                $document = new \DOMDocument('1.0', 'UTF-8');
                $root = $document->createElementNS($namespace, 'xliff');
                $root->setAttribute('version', '2.0');
                $document->appendChild($root);
                $file = $document->createElementNS($namespace, 'file');
                $file->setAttribute('id', 'f1');
                $root->appendChild($file);
                $file->appendChild($document->createElementNS($namespace, 'source', 'Hello'));
                return $document;
            },
            <<<'XML_DOCUMENT'
            <?xml version="1.0" encoding="UTF-8"?>
            <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0">
                <file id="f1">
                    <source>Hello</source>
                </file>
            </xliff>

            XML_DOCUMENT
            ,
        ];
    }

    /**
     * PHP 8.2 cannot remove a namespace declaration from a DOM element.
     */
    #[RequiresPhp('>= 8.3')]
    public function testRemovedNamespaceDeclarationIsNotPrinted(): void
    {
        $xml = "<root xmlns:a=\"A\">\n    <b xmlns:a=\"B\"/>\n    <c xmlns:a=\"A\"/>\n</root>\n";
        $document = (new DomDocumentFactory())->createFromXml($xml);
        $sourceMap = SourceMap::create($xml, $document);
        self::element($document, 'b')->removeAttribute('xmlns:a');

        self::assertSame(
            "<root xmlns:a=\"A\">\n    <b/>\n    <c xmlns:a=\"A\"/>\n</root>\n",
            (new XmlPrinter())->print($document, Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE), $sourceMap)
        );
    }

    public function testKeepsWindowsLineBreaks(): void
    {
        $xml = "<root>\r\n  <a\r\n    b=\"1\"/>\r\n\r\n  <!-- c -->\r\n</root>\r\n";

        self::assertSame(
            "<root>\r\n    <a\r\n        b=\"1\"/>\r\n\r\n    <!-- c -->\r\n</root>\r\n",
            self::print($xml, Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE))
        );
    }

    public function testKeepsTextWithBlanksBeforeWindowsLineBreaks(): void
    {
        $xml = "<root>\r\n    <p><b>x</b>  \r\n    more\r\n    lines</p>\r\n    <a>  \r\n    text</a>\r\n</root>\r\n";

        self::assertSame($xml, self::print($xml, Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE)));
    }

    public function testChangedTextUsesTheLineBreakOfTheFile(): void
    {
        $xml = "<root>\r\n    <a>old</a>\r\n</root>\r\n";
        $document = (new DomDocumentFactory())->createFromXml($xml);
        $sourceMap = SourceMap::create($xml, $document);
        self::element($document, 'a')->textContent = "line1\nline2";

        self::assertSame(
            "<root>\r\n    <a>line1\r\nline2</a>\r\n</root>\r\n",
            (new XmlPrinter())->print($document, Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE), $sourceMap)
        );
    }

    public function testUsesTheLineBreakOfTheFirstLine(): void
    {
        $xml = "<root>\n    <a>line1\r\nline2</a>\n    <b/>\n</root>\n";

        self::assertSame($xml, self::print($xml, Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE)));
    }

    public function testCdataWithItsEndMarkerIsPrintedTheSameOnTheNextRun(): void
    {
        $xml = "<root>\n    <a><![CDATA[x]]></a>\n</root>\n";
        $indent = Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE);
        $document = (new DomDocumentFactory())->createFromXml($xml);
        $sourceMap = SourceMap::create($xml, $document);
        $cdata = $document->getElementsByTagName('a')
            ->item(0)?->firstChild;
        self::assertInstanceOf(\DOMCdataSection::class, $cdata);
        $cdata->data = 'p]]>q';

        $printed = (new XmlPrinter())->print($document, $indent, $sourceMap);

        self::assertSame("<root>\n    <a><![CDATA[p]]]]><![CDATA[>q]]></a>\n</root>\n", $printed);
        self::assertSame($printed, self::print($printed, $indent));
    }

    public function testFileWithEntitiesOfADtdIsPrintedInTheFixedForm(): void
    {
        $xml = "<!DOCTYPE root [<!ENTITY name \"value\">]>\n<root>\n  <a>&name;</a>\n</root>\n";
        $document = (new DomDocumentFactory())->createFromXml($xml);

        self::assertNull(SourceMap::create($xml, $document));
        self::assertSame(
            "<?xml version=\"1.0\"?>\n<!DOCTYPE root [\n<!ENTITY name \"value\">\n]>\n<root>\n    <a>&name;</a>\n</root>\n",
            (new XmlPrinter())->print($document, Indent::fromSizeAndStyle(4, Indent::STYLE_SPACE))
        );
    }

    public function testPrintsOnlyFilesEncodedInUtf8(): void
    {
        $printer = new XmlPrinter();
        $factory = new DomDocumentFactory();

        self::assertTrue($printer->canPrint($factory->createFromXml('<root/>')));
        self::assertTrue($printer->canPrint($factory->createFromXml('<?xml version="1.0" encoding="utf-8"?><root/>')));
        self::assertFalse(
            $printer->canPrint($factory->createFromXml('<?xml version="1.0" encoding="iso-8859-1"?><root/>'))
        );
    }

    private static function print(string $xml, Indent $indent): string
    {
        $document = (new DomDocumentFactory())->createFromXml($xml);

        return (new XmlPrinter())->print($document, $indent, SourceMap::create($xml, $document));
    }

    /**
     * @return array{string, string}
     */
    private static function readFixture(string $fixture): array
    {
        $parts = explode("-----\n", FileSystem::read(__DIR__ . '/Fixtures/' . $fixture));

        return [$parts[0], $parts[1] ?? $parts[0]];
    }

    private static function element(\DOMDocument $document, string $name): \DOMElement
    {
        $element = $document->getElementsByTagName($name)
            ->item(0);
        self::assertInstanceOf(\DOMElement::class, $element);

        return $element;
    }
}
