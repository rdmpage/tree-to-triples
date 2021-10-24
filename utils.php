<?php


require_once(dirname(__FILE__) . '/vendor/autoload.php');

use ML\JsonLD\JsonLD;
use ML\JsonLD\NQuads;

//----------------------------------------------------------------------------------------
// Eventually it becomes clear we can't use b-nodes without causing triples tores to replicate
// lots of triples, so create arbitrary URIs using the graph URI as the base.
function create_bnode($graph, $type = "")
{
	global $use_bnodes;
	
	$bnode = null;
	$bytes = random_bytes(5);
	$node_id = bin2hex($bytes);
	$uri = '_:' . $node_id;

	if ($type != "")
	{
		$bnode = $graph->resource($uri, $type);
	}
	else
	{
		$bnode = $graph->resource($uri);
	}	

	return $bnode;
}

//----------------------------------------------------------------------------------------
// Make a URI play nice with triple store
function nice_uri($uri)
{
	$uri = str_replace('[', urlencode('['), $uri);
	$uri = str_replace(']', urlencode(']'), $uri);
	$uri = str_replace('<', urlencode('<'), $uri);
	$uri = str_replace('>', urlencode('>'), $uri);

	return $uri;
}



//----------------------------------------------------------------------------------------
// From easyrdf/lib/parser/ntriples
function unescapeString($str)
    {
        if (strpos($str, '\\') === false) {
            return $str;
        }

        $mappings = array(
            't' => chr(0x09),
            'b' => chr(0x08),
            'n' => chr(0x0A),
            'r' => chr(0x0D),
            'f' => chr(0x0C),
            '\"' => chr(0x22),
            '\'' => chr(0x27)
        );
        foreach ($mappings as $in => $out) {
            $str = preg_replace('/\x5c([' . $in . '])/', $out, $str);
        }

        if (stripos($str, '\u') === false) {
            return $str;
        }

        while (preg_match('/\\\(U)([0-9A-F]{8})/', $str, $matches) ||
               preg_match('/\\\(u)([0-9A-F]{4})/', $str, $matches)) {
            $no = hexdec($matches[2]);
            if ($no < 128) {                // 0x80
                $char = chr($no);
            } elseif ($no < 2048) {         // 0x800
                $char = chr(($no >> 6) + 192) .
                        chr(($no & 63) + 128);
            } elseif ($no < 65536) {        // 0x10000
                $char = chr(($no >> 12) + 224) .
                        chr((($no >> 6) & 63) + 128) .
                        chr(($no & 63) + 128);
            } elseif ($no < 2097152) {      // 0x200000
                $char = chr(($no >> 18) + 240) .
                        chr((($no >> 12) & 63) + 128) .
                        chr((($no >> 6) & 63) + 128) .
                        chr(($no & 63) + 128);
            } else {
                # FIXME: throw an exception instead?
                $char = '';
            }
            $str = str_replace('\\' . $matches[1] . $matches[2], $char, $str);
        }
        return $str;
    }
    
//----------------------------------------------------------------------------------------
function to_triples($graph)
{
	$format = \EasyRdf\Format::getFormat('ntriples');

	$serialiserClass  = $format->getSerialiserClass();
	$serialiser = new $serialiserClass();

	$triples = $serialiser->serialise($graph, 'ntriples');

	// Remove JSON-style encoding
	$told = explode("\n", $triples);
	$tnew = array();

	foreach ($told as $s)
	{
		$tnew[] = unescapeString($s);
	}

	$triples = join("\n", $tnew);
    
	return $triples;
}

//----------------------------------------------------------------------------------------
function to_jsonld($triples, $context, $type = 'http://schema.org/Thing' )
{
	// Frame document
	$frame = (object)array(
		'@context' => $context,
		'@type' => str_replace('schema:', 'http://schema.org/', $type)
	);	

	// Get simple JSON-LD
	$options = array();
	$options['context'] = $context;
	$options['compact'] = true;
	$options['frame']	= $frame;	

	// Use same libary as EasyRDF but access directly to output ordered list of authors
	$nquads = new NQuads();

	// And parse them again to a JSON-LD document
	$quads = $nquads->parse($triples);		
	$doc = JsonLD::fromRdf($quads);	
	$obj = JsonLD::frame($doc, $frame);
	
	$json = json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);		

	return $json;
}

?>
