<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Services;

/**
 * Simple line-by-line diff service.
 * Returns an array of diff lines with type: 'equal'|'added'|'removed'.
 */
class DiffService
{
    /**
     * Compute a line-by-line diff between two text strings.
     *
     * @return array<int, array{type: string, line: string, lineOld: int|null, lineNew: int|null}>
     */
    public function diff(string $oldText, string $newText): array
    {
        $oldLines = explode("\n", $this->normalize($oldText));
        $newLines = explode("\n", $this->normalize($newText));

        $lcs = $this->lcs($oldLines, $newLines);

        return $this->buildDiff($oldLines, $newLines, $lcs);
    }

    /**
     * Normalize line endings.
     */
    private function normalize(string $text): string
    {
        return str_replace(["\r\n", "\r"], "\n", $text);
    }

    /**
     * Compute the Longest Common Subsequence table.
     *
     * @param string[] $a
     * @param string[] $b
     * @return int[][]
     */
    private function lcs(array $a, array $b): array
    {
        $m = count($a);
        $n = count($b);

        // Use two rows to save memory
        $prev = array_fill(0, $n + 1, 0);
        $curr = array_fill(0, $n + 1, 0);

        // We need to store the full table for backtracking
        $table = [];
        for ($i = 0; $i <= $m; $i++) {
            $table[$i] = array_fill(0, $n + 1, 0);
        }

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $table[$i][$j] = $table[$i - 1][$j - 1] + 1;
                } else {
                    $table[$i][$j] = max($table[$i - 1][$j], $table[$i][$j - 1]);
                }
            }
        }

        return $table;
    }

    /**
     * Build the diff output by backtracking the LCS table.
     *
     * @param string[] $a
     * @param string[] $b
     * @param int[][]  $table
     * @return array<int, array{type: string, line: string, lineOld: int|null, lineNew: int|null}>
     */
    private function buildDiff(array $a, array $b, array $table): array
    {
        $result = [];
        $i = count($a);
        $j = count($b);

        while ($i > 0 || $j > 0) {
            if ($i > 0 && $j > 0 && $a[$i - 1] === $b[$j - 1]) {
                array_unshift($result, [
                    'type'    => 'equal',
                    'line'    => $a[$i - 1],
                    'lineOld' => $i,
                    'lineNew' => $j,
                ]);
                $i--;
                $j--;
            } elseif ($j > 0 && ($i === 0 || $table[$i][$j - 1] >= $table[$i - 1][$j])) {
                array_unshift($result, [
                    'type'    => 'added',
                    'line'    => $b[$j - 1],
                    'lineOld' => null,
                    'lineNew' => $j,
                ]);
                $j--;
            } else {
                array_unshift($result, [
                    'type'    => 'removed',
                    'line'    => $a[$i - 1],
                    'lineOld' => $i,
                    'lineNew' => null,
                ]);
                $i--;
            }
        }

        return $result;
    }
}
