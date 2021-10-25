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


## Data

### Mammal Species of the World

https://doi.org/10.15468/csfquc

