<?php

namespace DeployTracker\Tests\Parser;

use PHPUnit\Framework\TestCase;
use DeployTracker\Parser\CapistranoRevisionLogParser as Parser;
use DeployTracker\Exception\RevisionLogParseException;
use DeployTracker\Entity\Deployment;

class CapistranoRevisionLogParserTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->parser = new Parser();
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfLineIsNotParsable()
    {
        $this->expectException(RevisionLogParseException::class);
        $this->parser->parseLine('some bogus line');
    }

    /**
     * @test
     */
    public function shouldCreateDeploymentFromLine()
    {
        $expectedDate = \DateTime::createFromFormat(Parser::RELEASE_DATE_FMT, '20181224123456');

        $parsed = $this->parser->parseLine(
            'Branch master (at deadbeef) deployed as release 20181224123456 by someone'
        );

        self::assertTrue($parsed->isSuccess());
        self::assertSame('master', $parsed->getBranch());
        self::assertSame('deadbeef', $parsed->getCommitHash());
        self::assertSame('someone', $parsed->getDeployer());
        self::assertEquals($expectedDate, $parsed->getDeployDate());
    }

    /**
     * @test
     */
    public function shouldCreateRollbackFromLine()
    {
        $parser = new Parser();

        $parsed = $parser->parseLine('someotherguy rolled back to release 20181224123456');

        self::assertTrue($parsed->isRollback());
        self::assertSame('someotherguy', $parsed->getDeployer());
    }
}
