<?php

error_reporting(E_ALL);

require_once(dirname(dirname(__FILE__)) . '/utils.php');


//----------------------------------------------------------------------------------------

$filename = 'msw3-all-utf8.csv';

$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle
		);
				
	$go = is_array($row);
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;		
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					$v = strip_tags($v);
					$obj->{$headings[$k]} = $v;
				}
			}
			
			// name
			$name = '';
			switch ($obj->TaxonLevel)
			{
				case 'ORDER':
					$name = $obj->Order;
					break;

				case 'SUBORDER':
					$name = $obj->Suborder;
					break;

				case 'INFRAORDER':
					$name = $obj->Infraorder;
					break;

				case 'SUPERFAMILY':
					$name = $obj->Superfamily;
					break;

				case 'FAMILY':
					$name = $obj->Family;
					break;

				case 'SUBFAMILY':
					$name = $obj->Subfamily;
					break;
				
				case 'TRIBE':
					$name = $obj->Tribe;
					break;

				case 'GENUS':
					$name = $obj->Genus;
					break;

				case 'SUBGENUS':
					$name = $obj->Genus . ' (' . $obj->Subgenus . ')';
					break;

				case 'SPECIES':
					$name = $obj->Genus . ' ' . $obj->Species;
					break;

				case 'SUBSPECIES':
					$name = $obj->Genus . ' ' . $obj->Species . ' ' . $obj->Subspecies;
					break;
			
				default:
					break;
			}			
			
			$obj->name = $name;
			if (isset($obj->Author))
			{
				$obj->name .= ' ' . $obj->Author;
			}			
		
			// print_r($obj);
			
			if ($obj->ID == '10400148')	
			{
				//print_r($obj);
			}
			
			$graph = new \EasyRdf\Graph();
	
			// taxon
			$type = 'schema:Taxon';

			$taxon = $graph->resource('http://www.departments.bucknell.edu/biology/resources/msw3/browse.asp?id=' . $obj->ID, $type);	
			$taxon->add('schema:name', $obj->name);
			$taxon->add('schema:taxonRank', strtolower($obj->TaxonLevel));
		
			// MSW ID
			$identifier = create_bnode($graph, "schema:PropertyValue");		
			$identifier->add('schema:propertyID', 'msw');
			$identifier->add('schema:value', $obj->ID);
			$taxon->add('schema:identifier', $identifier);
		
			// scientific name
			$scientificName = create_bnode($graph, 'schema:TaxonName');
			$scientificName->add('schema:name', $obj->name);
			$scientificName->add('schema:taxonRank', strtolower($obj->TaxonLevel));
			$taxon->add('schema:scientificName', $scientificName);
			
			// comment
			if (isset($obj->Comments))
			{
				$comment = create_bnode($graph, 'schema:Comment');
			
				$text = $obj->Comments;
				$text = strip_tags($text);
			
				$comment->add('schema:text', $text);
				$taxon->add('schema:comment', $comment);
			}
			
			// synonyms
			if (isset($obj->Synonyms))
			{
				$text = $obj->Synonyms;
				$text = strip_tags($text);
				$synonyms = explode(';', $text);
				
				foreach ($synonyms as $synonym)
				{
					$taxon->add('schema:alternateScientificName', trim($synonym));
				}
			}
			
			// citation
			if (isset($obj->CitationName))
			{
				$citation = create_bnode($graph, 'schema:CreativeWork');
				$citation->add('schema:name', addcslashes($obj->CitationName, '"'));
			
				if (isset($obj->CitationVolume ))
				{
					$citation->add('schema:volumeNumber', $obj->CitationVolume);			
				}
				if (isset($obj->CitationIssue))
				{
					$citation->add('schema:issueNumber', $obj->CitationIssue);			
				}
				if (isset($obj->CitationPages))
				{
					$citation->add('schema:pagination', $obj->CitationPages);			
				}
				if (isset($obj->Author))
				{
					$citation->add('schema:creator', $obj->Author);			
				}
				if (isset($obj->Date))
				{
					$citation->add('schema:datePublished', $obj->Date);			
				}
				$scientificName->add('schema:isBasedOn', $citation);
			}
			
			$triples = to_triples($graph);
			
			if ($obj->ID == '10400148')	
			{
				//echo $triples . "\n";
			}
			
			echo $triples . "\n";
			
			/*
			$context = new stdclass;
			$context->{'@vocab'} = 'http://schema.org/';
			
			$json =  to_jsonld($triples, $context, $type);
			*/
			
			//echo $json . "\n";
		}
	}	
	$row_count++;
	
	//if ($row_count == 200) exit();
}
?>
