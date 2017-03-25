<?php

require 'vendor/autoload.php';

use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
use NlpTools\Utils\Normalizers\English;
use NlpTools\Stemmers\PorterStemmer;

class Shingler {
    static $stop_words = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

    function __construct($shingle_size) {
        $this->shingle_size = $shingle_size;
    }

    // concatenates metadata and content
    public function write_email_shingles2db() {
    	foreach (Email::all() as $email) {
     		$shingles = $this->transform2shingles($email->content.' '.$email->metadata);
 
     		foreach ($shingles as $str) {
         		$shingle = Shingle::find_or_create_by_content($str);
         		$email_shingle = EmailShingle::find(array('email_id' => $email->spamoutid, 'shingle_id' => $shingle->id));
 
 		        if (!$email_shingle) {
        	    	EmailShingle::create(array('email_id' => $email->spamoutid, 'shingle_id' => $shingle->id));
         		}
     		}
 		} 
    }

    public static function erase_shingles_from_db() {
        Shingle::query('SET FOREIGN_KEY_CHECKS = 0;');
        Shingle::query('TRUNCATE email_shingles;');
        Shingle::query('TRUNCATE shingles;');
        Shingle::query('SET FOREIGN_KEY_CHECKS = 1;');
    }

    private function normalize($document) {
        if (empty($document)) {
            throw new Exception('Analyzed text can not be empty');
        }

        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        $normalizer = new English();
        $stemmer = new PorterStemmer();

        $document_without_html = trim(strip_tags(str_replace('<', ' <', $document)));
        $document_without_symbols = preg_replace('/\W|_/', ' ', $document_without_html);
        $stop_words_pattern = '/\b(' . implode('|', self::$stop_words) . ')\b/';
        $tokens = $tokenizer->tokenize(preg_replace($stop_words_pattern, '', $document_without_symbols));
        $normalized_tokens = $normalizer->normalizeAll($tokens);
        
        return $stemmer->stemAll($normalized_tokens);
    }

    public function transform2shingles($document) {
        $tokens = $this->normalize($document);
        $shingles = new Ds\Set();;
        
        // NOTE: it should be ensured that vocabulary size is grater than or equal to shingle size
        $vocabulary_size = count($tokens);

        for ($i = 0; $i < $vocabulary_size - $this->shingle_size; $i++) {
            $shingles->add(implode(' ', array_slice($tokens, $i, $this->shingle_size)));
        }

        return $shingles;
    }
} 

