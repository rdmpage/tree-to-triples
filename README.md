# Trees to triples

Exploring ways of extracting taxonomic classifications as trees from databases (e.g., GBIF, MSW) then encoding them in RDF for fast querying (e.g., using nested set notation).

Idea is that we encode visit numbers in RDF so we can use that to query the tree structure rather than use property paths.

## Triple store

```
oxigraph_server -f .
```

```
curl 'http://localhost:7878/store?default' -H 'Content-Type:application/n-triples' --data-binary '@msw.nt'
```

```
curl 'http://localhost:7878/store?default' -H 'Content-Type:application/n-triples' --data-binary '@gmlreader/tree.nt'
```

## gmlreader

gmlreader is C++ code to process GML files and export them.

See also Vos, R. A. (2020). DBTree: Very large phylogenies in portable databases. Methods in Ecology and Evolution, 11(3), 457–463. [doi:10.1111/2041-210x.13337](https://doi.org/10.1111/2041-210x.13337)

## SPARQL

Get a subtree using first and rest counters

```
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX msw: <http://www.departments.bucknell.edu/biology/resources/msw3/browse.asp?id=>
SELECT ?taxon ?taxonName ?rank ?citation_name ?date WHERE {
 # <http://www.departments.bucknell.edu/biology/resources/msw3/browse.asp?id=13800444> ?p ?o .
  ?taxon rdf:first ?first .
  ?taxon rdf:rest ?rest .
  ?taxon schema:name ?taxonName .
  ?taxon schema:taxonRank ?rank .
  
  OPTIONAL {
    ?taxon schema:scientificName ?scientificName . 
    ?scientificName schema:isBasedOn ?citation .
    ?citation schema:name ?citation_name .
    ?citation schema:datePublished ?date .
  }
	# Rhinolophidae Gray
  FILTER (?first >= 17661 && ?rest <= 18120)
  
} 
ORDER BY ?first
```





## Data

### Mammal Species of the World

https://doi.org/10.15468/csfquc

