<?php

require_once(dirname(__FILE__) . '/tree.php');

// Parse MSW 
$paths = array();

$node_paths = array();
$node_labels = array();

$filename = 'msw3-all-utf8.csv';
$file_handle = fopen($filename, "r");
$count = 0;
while (!feof($file_handle)) 
{
	$parts = fgetcsv(
		$file_handle
		);
		
	$go = is_array($parts);
	
	if ($go)
	{
		
	
	if ($count == 0)
	{
		$size=count($parts);
		for ($i=0; $i < $size; $i++)
		{
			$heading[$parts[$i]] = $i;
		}
	}
	else
	{
		$id = $parts[$heading['ID']];
		
		$name = '';
		switch ($parts[$heading['TaxonLevel']])
		{
			case 'ORDER':
				$name = ucfirst(strtolower($parts[$heading['Order']]));
				break;

			case 'SUBORDER':
				$name = ucfirst(strtolower($parts[$heading['Suborder']]));
				break;

			case 'INFRAORDER':
				$name = ucfirst(strtolower($parts[$heading['Infraorder']]));
				break;

			case 'SUPERFAMILY':
				$name = ucfirst(strtolower($parts[$heading['Superfamily']]));
				break;

			case 'FAMILY':
				$name = ucfirst(strtolower($parts[$heading['Family']]));
				break;

			case 'SUBFAMILY':
				$name = ucfirst(strtolower($parts[$heading['Subfamily']]));
				break;
				
			case 'TRIBE':
				$name = ucfirst(strtolower($parts[$heading['Tribe']]));
				break;

			case 'GENUS':
				$name = $parts[$heading['Genus']];
				break;

			case 'SUBGENUS':
				$name = $parts[$heading['Genus']] . ' (' . $parts[$heading['Subgenus']] . ')';
				break;

			case 'SPECIES':
				$name = $parts[$heading['Genus']] . ' ' . $parts[$heading['Species']];
				break;

			case 'SUBSPECIES':
				$name = $parts[$heading['Genus']] . ' ' . $parts[$heading['Species']] . ' ' . $parts[$heading['Subspecies']];
				break;
			
			default:
				break;
		}

		// $node_labels[$id] = $name;	
		$node_labels[$id] = $id;		
	
		
		$path = '';
		for ($i = $heading['Order']; $i <= $heading['Subspecies']; $i++)
		{
			if ($parts[$i] != '')
			{
				$path .= "/" . $parts[$i];
			}
		}
		array_push($paths, $path);
		$node_paths[$path] = $id;		
	}
	$count++;
	}
	
}
fclose($file_handle);

//print_r($paths);

// Graph
$t = new Tree;
$t->PathsToTree($paths);


// GML

$edges= '';

echo 'graph [
comment "MSW"
directed 1
';

//echo 'node [id 0 label "Mammalia" ]' . "\n";

foreach ($t->nodes as $node)
{
	$id = $node_paths[$node->attributes['path']];
	
	if ($id == '')
	{
		echo 'node [id 0 label "Mammalia" ]' . "\n";
	}
	else
	{
		echo 'node [id ' . $id . ' label "' . $node_labels[$id] . '"]' . "\n";
	}
	//$node->dump();
	
	$anc = $node->GetAncestor();
	if ($anc)
	{
		$anc_id = $node_paths[$anc->attributes['path']];
		if ($anc_id == '')
		{
			$anc_id = 0;
		}
		$edges .= 'edge [ source ' . $anc_id . ' target ' . $id . ' ]' . "\n";
	}
}

echo $edges;
echo ']' . "\n";	




?>
