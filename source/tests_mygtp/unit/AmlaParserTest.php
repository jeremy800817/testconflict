<?php

use Snap\util\amla\MohaPdfParser;
use Snap\util\amla\BnmJsonParser;
use Snap\util\amla\UnXmlParser;

final class AmlaParserTest extends BaseTestCase
{
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
    }

    public function testMohaPdfParserCanParseDataFromRemoteFile()
    {
        $remoteFile = self::$app->getConfig()->{'moha.pdf.file'};

        $parser = new MohaPdfParser();
        $data = $parser->parseData($remoteFile);
        $this->assertNotNull($data);
        $this->assertNotEmpty($data);
    }

    public function testMohaPdfParserCanParseDataFromLocalFile()
    {
        $fileDir = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'files';
        $file = $fileDir . DIRECTORY_SEPARATOR . 'amlamoha.pdf';
        $parser = new MohaPdfParser();
        $data = $parser->parseData($file);
        $this->assertNotNull($data);
        $this->assertNotEmpty($data);
        $this->assertCount(45, $data);
    }

    public function testBnmJsonParserCanParseDataFromApi()
    {
        $parser = new BnmJsonParser();
        $data = $parser->parseData(null);
        $this->assertNotNull($data);
        $this->assertNotEmpty($data);
    }

    public function testBnmJsonParserCanParseDataFromLocalFile()
    {
        $fileDir = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'files';
        $file = $fileDir . DIRECTORY_SEPARATOR . 'amlabnm.json';
        $parser = new BnmJsonParser();
        $data = $parser->parseData($file);
        $this->assertNotNull($data);
        $this->assertNotEmpty($data);
        $this->assertCount(431, $data);
    }

    public function testUnXmlParserCanParseDataFromRemoteFile()
    {
        $remoteFile = self::$app->getConfig()->{'un.xml.file'};

        $parser = new UnXmlParser();
        $data = $parser->parseData($remoteFile);
        $this->assertNotNull($data);
        $this->assertNotEmpty($data);
    }

    public function testUnXmlParserCanParseDataFromLocalFile()
    {
        $fileDir = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'files';
        $file   = $fileDir . DIRECTORY_SEPARATOR . 'amlaun.xml';
        $parser = new UnXmlParser();

        $data = $parser->parseData($file);

        $sxe = simplexml_load_file($file);

        $this->assertNotNull($data);
        $this->assertNotEmpty($data);
        $this->assertEquals(count($sxe->INDIVIDUALS->INDIVIDUAL), count($data));
    }
}
