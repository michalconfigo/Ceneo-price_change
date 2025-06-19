<?php

namespace Ceneo\Domain\Service;

use Ceneo\Domain\Model\CurrentRunStats;
use Ceneo\Domain\Model\FeedGenerationConfiguration;
use Ceneo\Domain\Model\FeedGenerationRuntimeConfiguration;
use Ceneo\Domain\Model\TrustedOpinionsConfiguration;

class OptionService {

	public function saveConfiguration(FeedGenerationConfiguration $configuration): void {
		update_option("ceneo_plugin_configuration", $configuration);
	}

	public function getConfiguration(): FeedGenerationConfiguration {
		$feedGenerationConfiguration = get_option("ceneo_plugin_configuration");
		if ($feedGenerationConfiguration) return $feedGenerationConfiguration;
		return new FeedGenerationConfiguration();
	}

	public function saveRuntimeConfiguration(FeedGenerationRuntimeConfiguration $configuration): void {
		update_option("ceneo_plugin_runtime_configuration", $configuration);
	}

	public function getRuntimeConfiguration(): FeedGenerationRuntimeConfiguration {
		$feedGenerationRuntimeConfiguration = get_option("ceneo_plugin_runtime_configuration");
		if ($feedGenerationRuntimeConfiguration) return $feedGenerationRuntimeConfiguration;
		return new FeedGenerationRuntimeConfiguration();
	}

	public function saveCurrentRunStats(CurrentRunStats $currentRunStats): void {
		update_option("ceneo_plugin_current_run_stats", $currentRunStats);
	}

	public function getCurrentRunStats(): CurrentRunStats {
		$currentRunStats = get_option("ceneo_plugin_current_run_stats");
		if ($currentRunStats) return $currentRunStats;
		return new CurrentRunStats();
	}

	public function saveRunTime(int $time): void {
		update_option("ceneo_plugin_generator_run_time", $time);
	}

	public function getRunTime(): int {
		$runTime =  get_option("ceneo_plugin_generator_run_time");
		if ($runTime) return $runTime;
		return 0;
	}

	public function saveSyncTime(int $time): void {
		update_option("ceneo_plugin_synchronization_run_time", $time);
	}

	public function getSyncTime(): int {
		$runTime =  get_option("ceneo_plugin_synchronization_run_time");
		if ($runTime) return $runTime;
		return 0;
	}

	public function saveTrustedOpinionsConfiguration(TrustedOpinionsConfiguration $trustedOpinionsConfiguration): void {
		update_option("ceneo_plugin_trusted_opinions_configuration", $trustedOpinionsConfiguration);
	}

	public function getTrustedOpinionsConfiguration(): TrustedOpinionsConfiguration {
		$trustedOpinionsConfiguration =  get_option("ceneo_plugin_trusted_opinions_configuration");
		if ($trustedOpinionsConfiguration) return $trustedOpinionsConfiguration;
		return new TrustedOpinionsConfiguration();
	}

	public function saveCeneoSynchronizationInProgress(bool $inProgress): void {
		update_option("ceneo_synchronization_in_progress", $inProgress);
	}

	public function getCeneoSynchronizationInProgress(): bool {
		$inProgress = get_option("ceneo_synchronization_in_progress");
		if($inProgress) return $inProgress;
		return false;
	}

	public function saveFeedGenerationFrequency(string $frequency): void {
		update_option("ceneo_feed_generation_frequency", $frequency);
	}

	public function getFeedGenerationFrequency(): string {
		$frequency = get_option("ceneo_feed_generation_frequency");
		if($frequency) return $frequency;
		return "hourly";
	}

	public function getFileName(): string {
		$filename = get_option("ceneo_xml_filename");
		if ($filename) return $filename;
		$filename = uniqid(uniqid(uniqid('ceneo')));
		update_option("ceneo_xml_filename", $filename);
		return $filename;
	}

	public function saveApiKey(string $apiKey): void {
		if(!is_string($apiKey) || !strlen($apiKey) > 0 ) $apiKey = false;
		update_option("ceneo_api_key", $apiKey);
	}

	public function getApiKey(): string {
		$apiKey = get_option("ceneo_api_key");
		if($apiKey) return $apiKey;
		return '';
	}

	public function saveLastOrderSyncTime(int $time): void {
		if(!is_int($time) || $time < 0 ) $time = false;
		update_option("ceneo_last_sync_time", $time);
	}

	public function getLastOrderSyncTime(): int {
		$time = get_option("ceneo_last_sync_time");
		if($time) return $time;
		return time();
	}

	public function removeAllOptions(): void {
		delete_option('ceneo_plugin_configuration');
		delete_option('ceneo_plugin_runtime_configuration');
		delete_option('ceneo_plugin_current_run_stats');
		delete_option('ceneo_plugin_generator_run_time');
		delete_option('ceneo_plugin_synchronization_run_time');
		delete_option('ceneo_plugin_trusted_opinions_configuration');
		delete_option('ceneo_synchronization_in_progress');
		delete_option('ceneo_feed_generation_frequency');
		delete_option('ceneo_api_key');
	}
}
