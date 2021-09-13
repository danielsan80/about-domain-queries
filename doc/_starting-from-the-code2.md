## Partiamo da un po' di codice

Proviamo a partire con un po' di codice:

```
$children = $nodeProvider->byQuery(
    NodeQuery::create()
        ->sort(NodeQuery::POSITION, NodeQuery::ASC)
        ->slice(0, 100)
        ->setParentId((string)$parent->id())
);
```

Qui abbiamo un `NodeProvider` al quale chiediamo di darci una sottocollezione di `Node`
chiamando il metodo `byQuery(?NodeQuery $query): array`

> in particolare gli stiamo chiedendo "Dammi un blocco di 100 `Node` figli di `$parent`, ordinati per posizione".

Per specificare i criteri che il `NodeProvider` deve considerare per comporre la sottocollezione di cui
abbiamo bisogno usiamo una `NodeQuery`.

`NodeQuery` limita i criteri che possiamo specificare facendo sì che la query risultante copra solo quelli
che hanno senso nel nostro dominio, escludendo i casi non gestiti.

Al contrario un generico metodo `findBy(array $criteria): array` costringerebbe il `NodeProvider` a validare
il contenuto di `$criteria` mentre così viene fatta nella costruzione della `NodeQuery`.

`NodeQuery` è un value object immutabile che per la sua costruzione mette a disposizione
un'interfaccia fluida.

`NodeQuery` incapsula il concetto di query specifica per i `Node`, una query di dominio appunto.

> `$query = NodeQuery::create();` significa "Dammi tutti i nodi, l'ordine non mi interessa".
