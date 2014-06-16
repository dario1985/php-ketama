<?php

namespace Ketama;

class Ketama
{
    private $nodes;
    private $weights;
    private $hashring;
    private $sortedKeys;

    public function addNode($node, $weight = 1)
    {
        $this->nodes[] = $node;
        $this->weights[$node] = (int) $weight;

        $this->createContinuum();

        return $this;
    }

    public function getNode($key)
    {
        $ordinal = $this->getNodeOrdinal($key);
        if (null === $ordinal) {
            return null;
        }

        return $this->hashring[$this->sortedKeys[$ordinal]];
    }

    private function getNodeOrdinal($key)
    {
        if (!$this->hashring) {
            return null;
        }

        $key = $this->genKey($key);

        $nodes = $this->sortedKeys;
        $pos = $this->bisect($nodes, $key);

        if ($pos === count($nodes)) {
            return 0;
        }

        return $pos;
    }

    private function genKey($key)
    {
        $binKey = $this->digest($key);

        return $this->hashi(
            $binKey
        );
    }

    private function hashi($binKey)
    {
        return (($binKey[3] << 24)
            | ($binKey[2] << 16)
            | ($binKey[1] << 8)
            | $binKey[0]);
    }

    private function digest($key)
    {
        return array_map('ord', str_split(md5($key)));
    }

    private function bisect($nodes, $key)
    {
        $min = 0;
        $max = count($nodes);

        while ($min < $max) {
            $mid = (int) floor(($min + $max) / 2);
            if ($key < $nodes[$mid]) {
                $max = $mid;
            } else {
                $min = $mid + 1;
            }
        }

        return $min;
    }

    public function createContinuum()
    {
        $this->hashring = array();
        $this->sortedKeys = array();

        $total_weight = 0;
        foreach ($this->nodes as $node) {
            $total_weight += max(1, $this->weights[$node]);
        }

        $nodeCount = count($this->nodes);
        foreach ($this->nodes as $node) {
            $weight = max(1, $this->weights[$node]);
            $ks = floor((40 * $nodeCount * $weight) / $total_weight);

            for ($i = 0; $i <= $ks; $i++) {
                $binKey = $this->digest($node . '-' . $i . '-salt');

                for ($l = 0; $l <= 4; $l++) {
                    $key = (($binKey[3 + $l * 4] << 24)
                        | ($binKey[2 + $l * 4] << 16)
                        | ($binKey[1 + $l * 4] << 8)
                        | $binKey[$l * 4]);

                    $this->hashring[$key] = $node;
                    $this->sortedKeys[] = $key;
                }
            }
        }

        sort($this->sortedKeys);
    }
} 