<?php
namespace Grav\Plugin;

use Grav\Common\GPM\Response;

class PubMed {
	
	//the pubmed server
	var $host = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&retmode=json&id={IDS}';
		
	//initiate the pubmed vars
	var $response = null;
	var $summary = null;
	var $extract = null;
	var $title = null;
	var $authors = null;
	var $journal = null;
	var $volumes = null;
	var $pages = null;
	var $date = null;
	
	function PubMed() {
	}
	
	function get_summary($cache, $idstr) {
		$data = $cache->fetch('pubmed.'.$idstr);
		if ($data) {
			$this->summary = $data;
		} else {
			$host = str_replace( '{IDS}', $idstr, $this->host );
			$this->response = Response::get($host);
			$this->summary = json_decode($this->response, true);
			$cache->save('pubmed.'.$idstr, $this->summary);
		}

		//populate the extract
		$this->extract = [];
		foreach ($this->summary['result']['uids'] as $uid) {
			$this->extract[$uid] = [];
			$this->extract[$uid]['uid'] = $uid;
			if (array_key_exists('error', $this->summary['result'][$uid])) {
				$this->extract[$uid]['error'] = 'Could not find a record for UID '.$uid.'.';
			} else {
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
		}
		return $this;
	}
}

?>