*data: 2021-05-08*

...e alla fine abbiamo scoperto le Domain Queries
=================================================

Se penso a come spiegare tutta la storia di come siamo arrivati a questa soluzione mi prende un po' di sconforto.

Verrebbe un post lunghissimo e noioso che non ho voglia di scrivere e che voi non avreste voglia di leggere.

Proviamo a partire dal un po' di codice:

```
$children = $nodeProvider->byQuery(
    NodeQuery::create()
        ->sort(NodeQuery::POSITION, NodeQuery::ASC)
        ->slice($start, $length)
        ->setParentId((string)$parent->id())
);
```
#### $nodeProvider
Innanzi tutto c'è un `$nodeProvider`, istanza della classe di dominio `NodeProvider`.
`NodeProvider` è un POJO.

La keyword `provider` viene da una mutazione naturale del concetto di `repository`.
Qui infatti un Provider è inteso come un Repository in sola lettura, una collezione dalla quale si può ottenere,
attraverso i suoi metodi, un sottoinsieme degli elementi che contiene.

Altre keyword che potrebbero descrivere il concetto sono:
`SearchEngine`, `ReadOnlyRepository`, `Reader`, `ReadOnlyCollection`

#### byQuery()

Uno dei metodi che fa parte dell'interfaccia della classe `NodeProvider` è
`NodeProvider::byQuery(NodeQuery $query): array`

E' questo il modo più generico di ottenere dal provider una sottocollezione di elementi corrispondente
ai criteri che il nostro dominio ammette.

Il `NodeProvider` ha anche un immancabile metodo `NodeProvider::byId(NodeId $id): ?Node`. 

Potrebbe anche avere `NodeProvider::byParentId(NodeId $id): array`
oppure `NodeProvider::all(): array` ma sarebbero ridondanti poiché si possono ottenere
gli stessi risultati da `NodeProvider::byQuery(NodeQuery $query): array`.

Sono inoltre più rigidi perché ad esempio potrei avere la necessità di introdurre un ordinamento
o la paginazione del risultato e la modifica della signature risultante non sarebbe particolarmente
elegante: `NodeProvider::byParentId(NodeId $id, string $sortKey = 'label', string $sortDirection = 'asc', int $page = 0, int $perPage = 20): array`;

all'aggiunta di cosi tanti argomenti eterogenei si potrebbe preferire un generico `array $option` la cui validazione e i valori di default
andrebbero poi comunque gestiti nell'implementazione del metodo: `NodeProvider::byParentId(NodeId $id, array $options): array`;

oppure si preferirà astrarre la signature sostituiendo tutti gli argomenti con uno solo che rappresenta appunto
la richiesta di fare una query lasciando che validazione e valori di default siano gestiti
da questo DTO nel momento della sua creazione:
`NodeProvider::byParentId(ByParentIdQuery $query): array`.

Insomma long story short, arriveremmo ad avere un unico metodo `NodeProvider::byQuery(NodeQuery $query): array`
ma in ogni caso la complessità della creazione della query con la sua validazione
e la gestione dei valori di default sarebbe solo spostata nel costruttore di `NodeQuery`.

#### NodeQuery::create()

Che modo abbiamo di semplificare la costruzione di un `NodeQuery`? Possiamo usare un builder,
un `NodeQueryBuilder`, ma il concetto di `query` ha una piacevole caratteristica:
è valida anche se non specifichiamo nulla.

Per intenderci se `$query = new NodeQuery();` allora `$query` corrisponde a chiedere l'intera collezione
di nodi, non paginati e senza un ordine specifico.

Quindi possiamo creare un factory method `NodeQuery::create(): self` che ci restituisca una `NodeQuery`
senza vincoli e poi aggiungere diversi metodi per aggiungere o rimuovere criteri.

E' meglio che `NodeQuery` sia immutabile per cui questi metodi che aggiungono o rimuovono criteri
implementeranno un'interfaccia fluida e restituiranno sempre una copia della query con uno stato alterato.

Insomma, gli ingredienti che utilizziamo sono: `factory method`, `immutabilità`, `interfaccia fluida`.

#### NodeQuery->sort(NodeQuery::POSITION, NodeQuery::ASC)

Uno dei criteri che la nostra query potrebbe gestire è l'ordinamento.

Nell'implementazione del metodo `sort` possiamo validare ciò che il client ci sta passando.
Possiamo decidere qui quali sono i criteri per i quali ha senso ordinare i nostri elementi,
se vogliamo permettere l'ordinamento inverso o no, e lanceremo un'`InvalidArgumentException`
o un `LogicException` qualora il client tentasse di ordinare per un criterio non ammesso.
Nel caso della `NodeQuery` ammettiamo l'ordinamento per `position` e per `label` sia `asc` che `desc`
e il metodo accetta due stringe. I valori ammessi sono definiti come costanti di `NodeQuery`
e vengono validati nell'implementazione del metodo sort.
> La signature di `sort` potrebbe anche essere
> `NodeQuery::sort(NodeQuerySortKey $sortKey, NodeQuerySortDirection $sortDirection)`
> e spostare quindi la validazione dentro ai costruttori di `NodeQuerySortKey` e `NodeQuerySortDirection`.
> Ad oggi io trovo un tradeoff accettabile l'utilizzo di stringhe e costanti in favore
> di un codice più compatto. 












Partiamo da alcuni principi:
- Non vogliamo che il nostro codice dipenda da un query language di un servizio esterno: SQL ad esempio
- Non vogliamo neanche che il nostro codice dipenda un query language più astratto ma pur sempre di una
  libreria esterna: DQL ad esempio (Doctrine Query Language)
- Non vogliamo che il nostro query language sia in formato stringa:
  ci piacciono Doctrine e Mongo che ci mettono a disposizione un oggetti o array associativi per interrogarli
  perché sono facilmente componibili
- Non vogliamo che il nostro query language sia in formato di array: ok, preferiamo l'approccio di Doctrine che
  ci da un oggetto Query e un QueryBuilder per comporla.
- Vogliamo l'immutabilità per la nostra Query. 
- Vogliamo un Builder per costruire la nostra Query.
- Vogliamo limitare i criteri di interrogazione nella nostra query a quelli che riteniamo opportuni
  per una certa entità: per noi non ha senso ad esempio ordinare per id del nodo, puoi ordinare solo per posizione
  e per nome.
- Vogliamo separare le operazioni di scrittura da quelle di lettura della nostra collezione.

Detto questo partiamo dall'ultimo punto, figlio del CQRS.

Prendiamo il NodeRepository: dovrebbe avere sicuramente i metodi `add(Node $node) void` e `remove(Node $node): void`
per aggiungere e rimuovere nodi alla collezione, operazioni di modifica.
Dovrebbe poi avere anche i metodi per interrogare la collezione come `all(): array`, `byParentId(NodeId $node): array`,
`byId(NodeId $id): ?Node` e così via.

Oggi non riesco pià a vedere questi metodi come parte della stessa interfaccia, quindi definisco le interfacce
`NodeRepository` per le operazioni di modifica (e al più `byId(NodeId $id)` ma non ne sono convinto) e `NodeProvider`
per le operazioni di lettura. 

  

  

  




E' una storia che inizia con le query SQL fatte direttamente nelle pagine php, quando MVC, mod_rewrite o framework
non sapevo neanche che volessero dire. 

Poi è venuta la separazione del modello da vista e controller con un componente che si occupava solo di tirare fuori
i record dal DB, degli array associativi.

Poi è venuto fuori che il codice doveva essere agnostico rispetto al database che su usava per memorizzare i dati:
MySql, Postgres, Sqlite o magari uno di quei nuovi db non relazionali, i No Sql, i documentali...

Quindi sono arrivati gli ORM, o meglio è arrivato Doctrine e i record sono diventati entità e son venuti fuori
i suoi Repository. Così "in teoria" (ma molto in teoria) potevi ignorare che stavi scrivendo e leggendo le entità da
un database Mysql.

Poi mi son chiesto che fosse esattamente un Repository e Fowler ha detto che dovrebbe assomigliare 
a una collezione di entità dello stesso tipo in memoria. E si perché dato che devi ignorare che sotto c'è un database
reale devi far finta che i dati li stai scrivendo in memoria.

Quindi i Repository sono diventati interfacce con la loro implementazione per Doctrine, così c'era la 
UserRepositoryInterface (poi diventata semplicemente UserRepository) e le implementazioni
DoctrineUserRepository e InMemoryUserRepository.

Poi ho dovuto rinunciare alle hard relation di Doctrine perché proprio non si riusciva a farle funzionare con l'idea
delle collezioni isolate in memoria e ho dovuto passare alle soft relation:
sto parlando ad esempio dell'entità Node che non poteva avere il metodo `Node::getParent(): Node` ma che
doveva avere il metodo `Node::getParentId(): NodeId`.


### Concetti

- Saltiamo tutta la storia
- Il Repository e il Provider
- Il vantaggio della domain query rispetto a le generic query
- Immutabilità
- IDE friendly
- AbstractQuery e Traits ma anche no
