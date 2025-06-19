<?php


namespace Ceneo\Domain\Model;


class TrustedOpinionsConfiguration {
	private $questionnaireExpirationDays;
	private $ceneoGUID;

	/**
	 * @return mixed
	 */
	public function getQuestionnaireExpirationDays() {
		return $this->questionnaireExpirationDays;
	}

	/**
	 * @param mixed $questionnaireExpirationDays
	 */
	public function setQuestionnaireExpirationDays( $questionnaireExpirationDays ): void {
		$this->questionnaireExpirationDays = $questionnaireExpirationDays;
	}

	/**
	 * @return mixed
	 */
	public function getCeneoGUID() {
		return $this->ceneoGUID;
	}

	/**
	 * @param mixed $cenegoGUID
	 */
	public function setCeneoGUID( $cenegoGUID ): void {
		$this->ceneoGUID = $cenegoGUID;
	}

}
