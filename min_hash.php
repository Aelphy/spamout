<?php

require 'hasher.php';
require 'shingler.php';

class MinHash {
	function __construct($num_hashes, $max_value, $prime, $num_bands) {
    	$this->num_hashes = $num_hashes;
		$this->max_value = $max_value;
        $this->prime = $prime;
        $this->num_bands = $num_bands;
		$this->hash_tables = NULL;
        $this->band_size = $num_hashes / $num_bands;

		$this->hasher = new Hasher($this->num_hashes, $this->max_value, $this->prime);
        $this->hasher->build_hash_functions();
        // $hasher->save_hash_functions('coefficients/hash_coeffs'); NOTE: could be usefull to save hash_functions
	}

	public function query($document, $j_thr, $shingle_size) {
		if (!$this->hash_tables) {
			print('Build min hash first');
			return;
		}

        $shingler = new Shingler($shingle_size);
        $shingles = $shingler->transform2shingles($document);

        $intersected_shingles = array();

        foreach ($shingles as $shingle) {
            if ($known_shingle = Shingle::find_by_content($shingle)) {
                $intersected_shingles[] = $known_shingle;
            }  
        }


        if (!$intersected_shingles) {
            print('The document is fully unique');
            return array();
        }

        $signature = array_fill(0, $this->num_hashes, INF);

        for ($i = 0; $i < count($intersected_shingles); $i++) {
            $hash = $this->hasher->sign($intersected_shingles[$i]->id);
            
            for ($j = 0; $j < $this->num_hashes; $j++) {
                if ($hash[$j] < $signature[$j]) {
                    $signature[$j] = $hash[$j];
                }
            }
        }


        $neighbours_candidates = new Ds\Set();

        for ($i = 0; $i < $this->num_bands; $i++) {
            $band = array_slice($signature, $i * $this->band_size, $this->band_size);
            
            if ($this->hash_tables[$i]->hasKey($band)) {
                $neighbours_candidates = $neighbours_candidates->merge($this->hash_tables[$i][$band]);
            }
        }


        $neighbours = array();

        foreach($neighbours_candidates as $candidate) {
            $candidate_shingles = Email::find_by_spamoutid($candidate)->shingles;

            print($this->Jaccard_similarity($candidate_shingles, $intersected_shingles)); 
            if ($this->Jaccard_similarity($candidate_shingles, $intersected_shingles) >= $j_thr) {
                $neighbours[] = $candidate;
            }
        }

        return $neighbours;
    }

    public function Jaccard_similarity($shingles1, $shingles2) {
        $result = 0;
        $tmp1 = new Ds\Set();
        $tmp2 = new Ds\Set();

        foreach($shingles1 as $shingle1) {
            $tmp1->add($shingle1->id);
        }

        foreach($shingles2 as $shingle2) {
            $tmp2->add($shingle2->id);
        }

        return count($tmp1->intersect($tmp2)) / count($tmp1->merge($tmp2));
    }

    // It is assumed that each doc from collection has -> shingles
    //  also, doc here should have spamoutid - this can be fixed to be more universal
	public function build($documents) {
 		$hash_tables = array();
 
 		for ($i = 0; $i < $this->num_bands; $i++) {
     		$hash_tables[] = new Ds\Map();
 		}
 
 		foreach($documents as $doc) {
     		$signature = array_fill(0, $this->num_hashes, INF);
 
 	    	for ($i = 0; $i < count($doc->shingles); $i++) {
         		$hash = $this->hasher->sign($doc->shingles[$i]->id);
 
 		        for ($j = 0; $j < $this->num_hashes; $j++) {
        		     if ($hash[$j] < $signature[$j]) {
                		 $signature[$j] = $hash[$j];
             		 }
        		}		
     		}
 
     	    for ($i = 0; $i < $this->num_bands; $i++) {
        	    $band = array_slice($signature, $i * $this->band_size, $this->band_size);
 
         	    if (!$hash_tables[$i]->hasKey($band)) {
            	    $hash_tables[$i][$band] = new Ds\Set();
         	    }
 
         	    $hash_tables[$i][$band]->add($doc->spamoutid);
     	    }

		    $this->hash_tables = $hash_tables;
        }
    }
}

