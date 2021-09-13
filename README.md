*data: 2021-05-08*

...e alla fine abbiamo scoperto le Domain Queries
=================================================

## Introduzione

**tags**: `dev-post` `ddd` `php` `cqrs`
> **Nota:** Questo è un **dev-post**. 
Questo post contiene oltre che questo testo anche del codice eseguibile che è, in realtà, il vero post:
questo testo è più un commento al codice.

Non è necessario eseguire il codice ma in caso voleste farlo servono solo
Git, Docker e Docker Compose.

Dopo aver clonato il repository Git ed esserci entrato con

```
git clone git@github.com:danielsan80/about-domain-queries.git
cd about-domain-queries
```
eseguite una
```
docker-compose up
```
il che dopo aver scaricato le immagini necessarie avvierà il container
lanciando i test con PhpUnit, rilasciando il controllo a test terminati.

Sotto il namespace `Dan\Daneel` c'è il codice di esempio principale.

Eventuali altri namespace (`Dan\Golan`, `Dan\Hari`, `Dan\Salvor`, ...)
contengono il codice rifattorizzato o delle varianti.

Se durante la lettura qualcosa non torna, di seguito ci sono alcune digressioni che possono aiutare a dipanare i dubbi.
Tuttavia ne consiglio la lettura solo successivamente perché non direttamente pertinenti con l'oggetto del post:

- ...
- ...

## La Query

Proviamo a partire con un po' di codice:

```
$children = $nodeProvider->byQuery(
    NodeQuery::create()
        ->sort(NodeQuery::POSITION, NodeQuery::ASC)
        ->slice(0, 100)
        ->setParentId((string)$parent->id())
);
```

### $nodeProvider

Innanzi tutto c'è un `$nodeProvider`, istanza della classe di dominio `NodeProvider`.

La keyword `provider` viene da una mutazione naturale del concetto di `repository`.
Qui infatti un Provider è inteso come un Repository in sola lettura, una collezione dalla quale si può ottenere,
attraverso i suoi metodi, un sottoinsieme degli elementi che contiene (o uno specifico elemento).

Altre keyword che potrebbero descrivere il concetto sono:
`SearchEngine`, `ReadOnlyRepository`, `Reader`, `ReadOnlyCollection`

### byQuery()

Uno dei metodi che fa parte dell'interfaccia della classe `NodeProvider` è
`NodeProvider::byQuery(NodeQuery $query): array`

E' questo il modo più generico di ottenere dal provider una sottocollezione di elementi corrispondente
ai criteri che decidiamo di ammettere.

`NodeQuery` limita i criteri che possiamo specificare facendo sì che la query risultante copra solo quelli
che hanno senso nel nostro dominio, escludendo i casi non gestiti.

Il `NodeProvider` ha anche un metodo `NodeProvider::byId(NodeId $id): ?Node` e un
metodo `NodeProvider::byCode(Code $code): ?Node` i quali restituiscono uno specifico `Node` o `null`,
non una collezione di `Node`.

Il `NodeProvider` potrebbe anche avere `NodeProvider::byParentId(NodeId $id): array`
oppure `NodeProvider::all(): array` ma sarebbero ridondanti poiché si possono ottenere
gli stessi risultati da `NodeProvider::byQuery(NodeQuery $query): array`, configurando opportunamente
la `NodeQuery`.

### NodeQuery::create()

Che modo abbiamo di semplificare la costruzione di una `NodeQuery`? Possiamo usare un builder,
un `NodeQueryBuilder`, ma il concetto di `query` ha una piacevole caratteristica:
è valida anche se non specifichiamo nulla.

Per intenderci se `$query = new NodeQuery();` allora `$query` corrisponde a chiedere l'intera collezione
di nodi, non paginati e senza un ordine specifico: la query nasce valida.

Quindi possiamo creare un factory method `NodeQuery::create(): self` che ci restituisca una `NodeQuery`
senza vincoli e poi aggiungere diversi metodi per aggiungere o rimuovere criteri e
facendo sì che rimanga sempre valida.

E' meglio che `NodeQuery` sia immutabile per cui questi metodi che aggiungono o rimuovono criteri
implementeranno un'interfaccia fluida e restituiranno sempre una copia della query con uno stato alterato.

Insomma, gli ingredienti che utilizziamo sono: `factory method`, `immutabilità`, `interfaccia fluida`, `always valid`.

### ->sort(NodeQuery::POSITION, NodeQuery::ASC)

Uno dei criteri che la nostra query potrebbe gestire è l'ordinamento.

Nell'implementazione del metodo `sort` possiamo validare ciò che il client ci sta passando.
Possiamo decidere qui quali sono i criteri per i quali ha senso ordinare i nostri `Node`
e se vogliamo permettere l'ordinamento decrescente o meno.

Notificheremo al dev tramite un'eccezione se sta cercando di utilizzare impropriamente questo metodo.   

In questo esempio abbiamo decido che i `Node` possono essere ordinati per `position` o per `label`,
sia in ordine crescente che decrescente.

### ->slice(0, 100)

Per tanto tempo ho pensato che a livello di dominio non si dovesse prevedere la paginazione dei risultati
perché si trattava di una necessità meramente tecnica:
non posso caricare in memoria un array di 33.000 elementi.

Alla fine mi sono arreso al fatto che la cosa più pratica da fare è tenere conto della paginazione
a livello di dominio.

Il metodo `slice` permette di selezionare un chunk del risultato della query, specificando l'offset (`start`)
e quanti elementi ritornare da lì in poi (`length`).

Un'implementazione alternativa (o ulteriore) del concetto di slicing potrebbe essere quella di passare il `NodeId`,
da cui partire e il numero di `Node` da ritornare, dato che è più performante quando c'è di mezzo un B-tree
(e alla fine c'è sempre).

### ->setParentId((string)$parent->id())

Abbiamo scelto un ordinamento, abbiamo paginato il risultato e ora specifichiamo uno dei filtri disponibili.

Qui chiediamo solo i nodi figli di un dato nodo.

Tra i vari metodi di filtro c'è anche `setRootOnly()` che di fatto è un alias si `setParentId(null)`.

E' evidente che se aggiungessi `setRootOnly()` e `setParentId((string)$parent->id()`
alla stessa query l'intersezione dei due filtri sarebbe sempre vuota. Per questo motivo
ho deciso che non possono coesistere nella stessa query e che si escluderanno a vicenda
notificando l'errore logico al dev che lo commetta.

### Riassumendo...

...quello che sto cercando di dire è che è possibile modellare il concetto di query lecita
per una certa collezione di elementi a livello di dominio.

Potrebbe esserci una specifica query per ogni collezione (e quindi per ogni provider):
`NodeQuery`, `ProductQuery`, `BrandQuery`.

Oppure una query potrebbe essere condivisa da più provider: ad esempio la `ProductQuery` potrebbe
essere passata al `ProductProvider` ma anche al `BrandProvider` al fine di ottenere, nel secondo caso,
tutti i `Brand` che corrispondono ai `Product` filtrati per una `ProductQuery`
dopo aver rimosso un eventuale filtro sul brand (`$brandQuery = $productQuery->removeBrand()`).

Comunque una volta che abbiamo un oggetto che rappresenta una query di dominio valida il lavoro che resta da fare
in un provider per convertirla in una query Sql, in una query Mongo o Mango, o applicarla ad un array in memoria
è un gioco da ragazzi.


## Il Provider

...

