<?php

namespace mmo\sf\Command;

use Aws\S3\S3Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class S3CreateBucketCommand extends Command
{
    protected static $defaultName = 'mmo:s3:create-bucket';
    protected static $defaultDescription = 'Creates a S3 bucket';

    /** @var S3Client */
    private $s3Client;

    public function __construct(S3Client $s3Client, string $name = null)
    {
        parent::__construct($name);

        $this->s3Client = $s3Client;
    }

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription)
            ->addArgument('bucket', InputArgument::REQUIRED, 'The name of the bucket to create')
            ->addOption('public', null, InputOption::VALUE_NONE, 'Mark a bucket as public (everyone can download files)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bucket = $input->getArgument('bucket');
        $this->s3Client->createBucket(['Bucket' => $bucket]);

        if ($input->getOption('public')) {
            $this->s3Client->putBucketPolicy(
                [
                    'Bucket' => $bucket,
                    'Policy' => $this->getPublicPolicy($bucket),
                ]
            );
        }
        return 0;
    }

    private function getPublicPolicy(string $bucket): string
    {
        return <<<EOS
{
    "Version":"2012-10-17",
    "Statement":[
        {
            "Sid":"PublicRead",
            "Effect":"Allow",
            "Principal": "*",
            "Action":["s3:GetObject"],
            "Resource":["arn:aws:s3:::$bucket/*"]
        }
    ]
}
EOS;
    }
}
