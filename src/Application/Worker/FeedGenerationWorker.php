<?php

namespace Ceneo\Application\Worker;

use Ceneo\Domain\Service\FeedGeneratorService;

class FeedGenerationWorker
{
    private $feedGeneratorService;

    public function __construct() {
        $this->feedGeneratorService = new FeedGeneratorService();
    }

    public function generationStep() {
        $this->feedGeneratorService->generateChunk();
    }
}