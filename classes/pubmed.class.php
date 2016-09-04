<?php
namespace Grav\Plugin;

class PubMed {
	
	//the geoPlugin server
	var $host = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&retmode=json&id={IDS}';
		
	//initiate the geoPlugin vars
	var $response = null;
	var $summary = null;
	var $extract = null;
	var $title = null;
	var $authors = null;
	var $journal = null;
	var $volumes = null;
	var $pages = null;
	var $date = null;
	var $cache = [];
	
	function PubMed() {

	}
	
	function get_summary($idstr) {
		if(array_key_exists($idstr, $this->cache)) {
			$this->summary = $this->cache[$idstr];
		} else {
			$host = str_replace( '{IDS}', $idstr, $this->host );
			$this->response = $this->fetch($host);
			$this->summary = json_decode($this->response, true);
			$this->cache[$idstr] = $this->summary;
		}

		//populate the extract
		$this->extract = [];
		foreach ($this->summary['result']['uids'] as $uid) {
			$this->extract[$uid] = [];
			$this->extract[$uid]['title'] = $this->summary['result'][$uid]['title'];
			$this->extract[$uid]['authors'] = [];
			foreach ($this->summary['result'][$uid]['authors'] as $author) {
				$name = $author['name'];
				array_push($this->extract[$uid]['authors'], $name);
			}
			$this->extract[$uid]['journal'] = $this->summary['result'][$uid]['fulljournalname'];
			$this->extract[$uid]['volume'] = $this->summary['result'][$uid]['volume'];
			$this->extract[$uid]['pages'] = $this->summary['result'][$uid]['pages'];
			$this->extract[$uid]['date'] = $this->summary['result'][$uid]['pubdate'];
		}
		return $this;
	}
	
	function fetch($host) {

		if ( function_exists('curl_init') ) {
						
			//use cURL to fetch data
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'PubMed PHP class');
			$response = curl_exec($ch);
			curl_close ($ch);
			
		} else if ( ini_get('allow_url_fopen') ) {
			
			//fall back to fopen()
			$response = file_get_contents($host, 'r');
			
		} else {

			trigger_error ('PubMed class Error: Cannot retrieve data. Either compile PHP with cURL support or enable allow_url_fopen in php.ini ', E_USER_ERROR);
			return;
		
		}
		
		return $response;
	}
}

?>