<?php

namespace Kainex\WiseAnalytics\Services\Commons;

trait ReportsDataHelper {

	protected function getGroupingExpressionByPeriod(string $period, string $column): string {
		switch ($period) {
			case 'daily':
				$groupExpression = 'DATE_FORMAT('.$column.', \'%%Y-%%m-%%d\')';
				break;
			case 'weekly':
				$groupExpression = 'DATE_FORMAT('.$column.', \'%x-%v\')';
				break;
			case 'monthly':
				$groupExpression = 'DATE_FORMAT('.$column.', \'%Y-%m-01\')';
				break;
			default:
				throw new \Exception('Invalid period: ' . $period);
		}

		return $groupExpression;
	}

	protected function fillResultsWithZeroValues(array $results, string $period, \DateTime $sourceStartDate, \DateTime $sourceEndDate, array $default = []): array {
		$byDate = [];
		foreach ($results as $record) {
			$byDate[$record->date] = $record;
		}
		$startDate = clone $sourceStartDate;
		$endDate = clone $sourceEndDate;

		$filledResults = [];
		if ($period === 'daily') {
			$endDate->modify('+1 day');
			while ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
				$dateStr = $startDate->format('Y-m-d');
				$filledResults[] = array_merge([ 'date' => $dateStr ], (array) ($byDate[$dateStr] ?? $default));
				$startDate->modify('+1 day');
			}
		} else if ($period === 'weekly') {
			$endDate->modify('+1 day');
			while ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
				$dateStrWeek = $startDate->format('Y-W');

				if (!isset($filledResults[$dateStrWeek])) {
					$filledResults[$dateStrWeek] = array_merge([ 'date' => $dateStrWeek ], (array) ($byDate[$dateStrWeek] ?? $default));
				}

				$startDate->modify('+1 day');
			}
			ksort($filledResults);

			$filledResultsAdjusted = [];
			foreach ($filledResults as $dateStrWeek => $results) {
				$dateStrWeekExploded = explode('-', $dateStrWeek);
				$date = (new \DateTime())->setISODate($dateStrWeekExploded[0], intval($dateStrWeekExploded[1]));

				$results['date'] = $date->format('Y-m-d');
				$results['week'] = $dateStrWeek;

				$filledResultsAdjusted[] = $results;
			}
			$filledResults = $filledResultsAdjusted;
		} else if ($period === 'monthly') {
			$endDate->modify('+1 day');
			while ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
				$dateStrMonth = $startDate->format('Y-m-01');

				if (!isset($filledResults[$dateStrMonth])) {
					$filledResults[$dateStrMonth] = array_merge([ 'date' => $dateStrMonth ], (array) ($byDate[$dateStrMonth] ?? $default));
				}

				$startDate->modify('+1 day');
			}
			ksort($filledResults);

			$filledResults = array_values($filledResults);
			if (count($filledResults) > 0) {
				$filledResults[0]['date'] = $sourceStartDate->format('Y-m-d');
			}
			if (count($filledResults) > 1) {
				$filledResults[count($filledResults) - 1]['date'] = $sourceEndDate->format('Y-m-d');
			}
		}

		return array_values($filledResults);
	}

}