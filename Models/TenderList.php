<?php

namespace DiplomaProject\Models;

class TenderList
{
    private $total_tenders_count = 0;
    private ?array $numbers_of_existing = null;

    /**
     * @param Tender[] $tenders
     */
    public function __construct(
        private array $tenders = [],
        private int $user_id = 0
    ) {
        $this->total_tenders_count = count($tenders);
    }

    /**
     * @return Tender[] $tenders
     */
    public function getTenders(): array
    {
        return $this->tenders;
    }

    public function isEmpty(): bool
    {
        return ($this->total_tenders_count <= 0);
    }

    /**
     * returns the publication numbers of the tenders that exist in the database
     *
     * @return string[]
     */
    public function getNumbersOfExisting(bool $reset_cache = false): array
    {
        if (null === $this->numbers_of_existing || $reset_cache) {
            $pub_nums = array_column($this->tenders, 'publication_number');

            $cond = [
                ['publication_number', 'IN', $pub_nums],
                ['user_id', '=', $this->user_id],
            ];

            $numbers_of_existing = TenderToUser::find($cond);
            $this->numbers_of_existing = array_column($numbers_of_existing, 'publication_number');
        }

        return $this->numbers_of_existing;
    }

    /**
     * returns true if the tender is with $publication_number is saved in the database.
     * this method only works with tenders from this list,
     * for other tenders it always returns false
     */
    public function isTenderSaved(string $publication_number): bool
    {
        $pub_nums = $this->getNumbersOfExisting();

        return (false !== array_search($publication_number, $pub_nums));
    }

    /**
     * returns array in the format:
     * ```php
     * [
     *     (int) publication_number => 'saved'
     *     (int) publication_number => 'saved'
     *     (int) publication_number => 'failed'
     *     (int) publication_number => 'saved'
     *     (int) publication_number => 'already-exists'
     * ]
     * ```
     *
     * @return string[]
     */
    public function saveList(): array
    {
        $saving_status = [];
        foreach ($this->getTenders() as $tender) {
            $pub_num = $tender->publication_number;

            if (!$this->isTenderSaved($pub_num)) {
                if ($tender->save()) {
                    $saving_status[$pub_num] = 'saved';
                } else {
                    $saving_status[$pub_num] = 'failed';
                }
            } else {
                $saving_status[$pub_num] = 'already-exists';
            }

            $tender_to_user = new TenderToUser();
            $tender_to_user->publication_number = $pub_num;
            $tender_to_user->user_id = $this->user_id;
            $tender_to_user->save();
        }

        return $saving_status;
    }
}
