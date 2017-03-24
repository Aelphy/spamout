<?php

require 'hasher.php';

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

	public function query($document) {
		if (!$this->hash_tables) {
			print('Build min hash first');
			return;
		}

		
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

