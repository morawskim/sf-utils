<?php

namespace mmo\sf\tests\Command;

use Aws\S3\S3Client;
use mmo\sf\Command\S3CreateBucketCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class S3CreateBucketCommandTest extends TestCase
{
    public function testCreateBucket(): void
    {
        $bucket = 'foo';

        $s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->addMethods(['createBucket'])
            ->getMock();
        $s3ClientMock->expects(self::once())
            ->method('createBucket')
            ->with(['Bucket' => $bucket]);

        $commandTester = new CommandTester(new S3CreateBucketCommand($s3ClientMock));
        $commandTester->execute([
            'bucket' => $bucket,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testCreatePublicBucket(): void
    {
        $bucket = 'foo';

        $s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->addMethods(['createBucket', 'putBucketPolicy'])
            ->getMock();
        $s3ClientMock->expects(self::once())
            ->method('createBucket')
            ->with(['Bucket' => $bucket]);
        $s3ClientMock->expects(self::once())
            ->method('putBucketPolicy')
            ->with(self::callback(function($args) use($bucket) {
                return $bucket === $args['Bucket']
                    && array_key_exists('Policy', $args)
                    && is_string($args['Policy']);
            }));

        $commandTester = new CommandTester(new S3CreateBucketCommand($s3ClientMock));
        $commandTester->execute([
            'bucket' => $bucket,
            '--public' => 1,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testSkipIfBucketExists(): void
    {
        $bucket = 'foo';

        $s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->addMethods(['createBucket'])
            ->onlyMethods(['doesBucketExist'])
            ->getMock();
        $s3ClientMock->expects(self::once())
            ->method('doesBucketExist')
            ->willReturn(true);
        $s3ClientMock->expects(self::never())
            ->method('createBucket');

        $commandTester = new CommandTester(new S3CreateBucketCommand($s3ClientMock));
        $commandTester->execute([
            'bucket' => $bucket,
            '--skip-if-exists' => 1,
        ], ['capture_stderr_separately' => true]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('already exists', $commandTester->getErrorOutput());
    }
}
