*data: 2021-05-08*

...e alla fine abbiamo scoperto le Domain Queries
=================================================

## Introduzione

**tags**: `dev-post` `ddd` `php` `cqrs`
> **Nota:** Questo è un **dev-post**. 
Questo post contiene oltre che questo testo anche del codice eseguibile che in realtà il vero post
mentre questo è solo un commento ad esso.

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

# La Query

Questo dovrebbe permetterci di andare subito al punto:

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

Per comprendere alcuni aspetti che forse lascino un po' perplessi facciamo un passo indietro...

## Il Repository pattern
Partiamo dalla [definizione di Repository](https://martinfowler.com/eaaCatalog/repository.html) presente su [martinfowler.com](martinfowler.com):

> Il Repository media tra il dominio e i Data Mapping layer usando
una collection-like interface per accedere agli oggetti di dominio.
 
Successivamente leggiamo che, più specificamente, un repository
dovrebbe agire come se fosse una collezione di oggetti in memoria.

Quindi l'interfaccia di un repository dovrebbe prevedere i metodi per aggiungere e
rimuovere oggetti alla collezione ma anche i metodi per poterla interrogare.

Nell'esempio si parla di un "Criteria" precedentemente costruito e configurato
che viene inviato al repository affinché restituisca la giusta collezione di oggetti.

Io e i miei colleghi abbiamo cercato di applicare questo pattern ai nostri progetti
e le soluzioni che abbiamo implementato si sono evolute di progetto in progetto
fino a che abbiamo raggiunto l'attuale grado di consapevolezza.

## Fissiamo alcuni punti

**Un repository gestisce una collezione di oggetti dello stesso tipo**

Ad esempio quindi il `NodeRepository` conterrà solo oggetti di tipo `Node`.

Non mi sembra che nell'articolo di Fowler ci sia questa precisazione e forse
potrebbe sembrare ovvia ma dai database documentali è possibile ottenere
documenti eterogenei e magari si potrebbe pensare la stessa cosa di un repository.

**Un repository a livello di dominio è solo un'interfaccia**

Ad esempio quindi potremmo avere l'interfaccia `NodeRepository`

**l'interfaccia di un repository viene implementata a livello infrastrutturale attraverso un adapter
  per una certa tecnologia**

Per l'interfaccia `NodeRepository` potremmo quindi avere le implementazioni concrete
`MysqlNodeRepository`, `MongoNodeRepository`, `InMemoryRepository`, ...
che persistono su, e ottengono da, una tecnologia target la collezione di `Node` 

**Gli oggetti di dominio devono essere modellati in modo che le relazioni tra di loro siano gestite
attraverso i loro id.**

Non sarà possibile quindi fare `$node->parent()->code()`.
ma sarà necessario fare `$nodeRepository->byId($node->parentId())->code()`.

In altre parole non vogliamo poter navigare le relazioni come ad esempio è possibile fare con Doctrine.

## CQRS

Siccome ultimamente siamo stati contaminati dalle logiche del CQRS, ha cominciato a suonarci male
l'idea che un repository si occupi sia della scrittura che della lettura degli oggetti in collezione
(beh, oltre che a suonarci male ci ha anche dato qualche problema reale).

Comunque siamo arrivati alla conclusione che l'interfaccia dei Repository andasse spezzata in due:
quella di scrittura e quella di lettura

Forse abbiamo sbagliato ma l'interfaccia per modificare la collezione abbiamo continuato a chiamarla `Repository`
mentre quella per la sola lettura l'abbiamo chiamata `Provider`.

Ad esempio quindi avremo un `NodeRepository` con i metodi per aggiungere e rimuovere i `Node` alla
collezione ed un `NodeProvider` con i metodi per ottenere uno specifico `Node` o un sottoinsieme della collezione
gestita dal repository.

## Il Dominio

Ho menzionato già troppe volte il `NodeRepository` e le sue varie declinazioni senza dire cos'è un
`Node` nel dominio che sto utilizzando come esempio:

>Abbiamo una collezione di `Node` che rappresentano i nodi di un albero.
> 
> Un `Node`
> può avere un `parent` oppure no, in questo secondo caso sarà un nodo radice (e quindi idealmente figlio di
> un ipotetico `RootNode`).
> 
> Un `Node` ha 0:n figli, ossia tutti quelli che lo hanno come `parent`.
> 
> Un `Node` è identificabile oltre che dal proprio `id` anche da un `code` (con alcuni vincoli).
> 
> Un `Node` ha una `label` che lo descrive in linguaggio naturale e una `position` per poterlo eventualmente
> ordinare rispetto ai suoi fratelli.

## Il Codice

Per proseguire ora ho bisogno di un po' di codice.

> Questa parte descrive la struttura delle directory e la funzione delle classi quindi credo
che possa essere **saltata** per tornarci eventualmente qualora qualcosa non fosse chiaro. 

In `/project/src` possiamo trovare il codice sorgente di esempio mentre in `/project/tests` il codice di test.

In `Dan\Daneel\Node\Domain` c'è il codice di dominio del contesto `Node` della versione `Daneel`.

Proseguendo in `Node` troviamo il codice del sottocontesto `Node` e in particolare in `Model`
c'è tutto il codice relativo al modello del `Node`.

La classe `Dan\Daneel\Node\Domain\Node\Model\Node` è la nostra entità `Node` di esempio che ha le caratteristiche
precedentemente descritte.

L'interfaccia `Dan\Daneel\Node\Domain\Node\Model\Repository\NodeRepository` non verrà usata in questo esempio
poiché ci concentreremo sulla lettura, per cui non vi è alcuna sua implementazione. In realtà non è necessario avere
sempre un repository se il nostro modulo prevede solo la lettura.

L'interfaccia `Dan\Daneel\Node\Domain\Node\Model\Provider\NodeProvider` ha appena 3 metodi, 2 per ottenere
esattamente un `node` e solo un altro per ottenere una sottocollezione di `Node`.
Quest'ultimo metodo come avrete intuito è quello che ci interessa maggiormente per l'argomento trattato ma ci
torneremo in seguito.

Abbiamo due value object `Dan\Daneel\Node\Domain\Node\Model\Code\Code` e `Dan\Daneel\Node\Domain\Node\Model\Id\NodeId`
da cui dipende `Node`, il primo per vincolare il `code` del `Node` a rispettare alcune regole e il secondo per definire
l'`id` del `Node` al fine di vincolarlo a contenere uno `uuid` e soprattutto per avere un riferimento
fortemente tipizzato.

Potrebbe lasciare un po' perplessi il namespace `Dan\Daneel\Node\Domain\Node\Model\Provider\Infrastructure` perché
non ci aspettavamo di trovare la keyword `Infratracture` dentro a `Domain`. In realtà questo è un escamotage per tenere
il codice infrastrutturale vicino a quello a cui si riferisce e rendendone più semplice la ricerca tramite navigazione
via File System.
In pratica usiamo la keyword `Infrastructure` in `append mode` creando isole di codice infrastrutturale dove serve. 
La stessa cosa viene fatta con la keyword `Testing` con la quale creiamo isole di codice utile ai test
(sia in `src` che in `test`).
Questo viola la separazione in layer `Domain`, `Application`, `Infrastructure`, `<Port>`, ... tipica del DDD?
Io non credo, perché questa separazione dovrebbe avvenire per forza a livello di file system?
In questo modo è anche possibile spostare il codice infrastrutturale in un package composer separato qualora fosse
necessario.
Nulla poi vieta di spostare il codice da `Dan\Daneel\Node\Domain\Node\Model\Provider\Infrastructure`
a `Dan\Daneel\Node\Infrastructure\Domain\Node\Model\Provider` di fatto senza violare la regola dell'`append mode`
suddetta.

In `Dan\Daneel\Node\Domain\Node\Model\Provider\Infrastructure\InMemory\InMemoryNodeProvider` troviamo l'implementazione
infrastrutturale del `NodeProvider` per la tecnologia target `InMemory`.
In `append mode`, dopo aver appeso `Infrastructure` al namespace viene appesa una keyword che identifichi
la tecnologia target seguendo il pattern `**\Infrastructure\**\<TargetTecnology>\**\<Implementation>`
dove `<Implementation>` è in genere composto da `<TargetTecnology><TargetIterface>` ma non necessariamente.

In questo caso implementiamo un provider che legge i `Node` da un array in memoria passato nel suo costruttore.
Questa implementazione non verrà mai usata in produzione (tant'è che forse dovrebbe stare in `Testing` anziché
in `Infrastructure`) ma è utile per descrivere in dettaglio, molto più di quanto possa fare
l'interfaccia `NodeProvider`, come si devono comportare le altre implementazioni di `NodeProvider`. 

Eh si perché in `Dan\Daneel\Node\Domain\Node\Model\Provider\Testing\NodeProviderTestCase` proviamo ad usare
un generico `NodeProvider` e in
`Tests\Dan\Daneel\Node\Domain\Node\Model\Provider\Infrastructure\InMemory\InMemoryNodeProviderTest`, che estende il
`NodeProviderTestCase` eseguiamo i test sull'`InMemoryProvider` accertandoci che si comporti come ci aspettiamo.
Per la proprietà transitiva avremo la "certezza" che ogni altro `NodeProvider` che venga testato estendendo
il `NodeProviderTestCase` si comporti "esattamente" come l'`InMemoryNodeProvider`. 
"certezza" e "esattamente" (tra virgolette) perché dipende da quanto profondamente il `NodeProviderTestCase` copre
tutti i casi possibili.



...



-----

Nel corso della mia vita professionale quello che forse mi ha dato più grattacapi è stata la persistenza dei dati
su un database e ottenerli da esso successivamente.

Cioè, è facile interagire con un database, ma la cosa diventa un po' più complicata quando vogliamo organizzare
il nostro codice in modo da applicare le buone pratiche che ci permettono di astrarre i concetti e rendere
il database utilizzato un *dettaglio implementativo*.

Il dettame a cui mi riferisco è 
> Il tuo codice deve essere agnostico rispetto al database. 

Un ORM come Doctrine ci permette di astrarre il SQL e di usare linguaggio più agnostico, il DQL.

Il DQL è simile al SQL ma parla a oggetti ignorando quale DBMS stiamo utilizzando:
possiamo chiedere tutti gli oggetti di una certa classe e non tutti i record di una certa tabella.

Inoltre abbiamo un QueryBuilder che ci permette di costruire o modificare una query con chiamate a metodi
anziché manipolando una stringa.

Tutto molto comodo, non dipendiamo più da un DBMS specifico, possiamo gestire tutti quelli **che Doctrine
ci permette di gestire** e siamo nel dominio degli oggetti... però...

Però ora dipendiamo da Doctrine che è una libreria esterna molto complessa e il problema rimane se
vogliamo passare a un database che Doctrine non gestisce con la stessa interfaccia. 

Un altro dettame che dovremmo seguire è
> Il tuo codice non deve dipendere da qualcosa che non puoi sostituire o che non sei in grado di
> mantenere personalmente
 
... 





---




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




---







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

  

  ---

  




E' una storia che inizia con le query SQL fatte direttamente nelle pagine php, quando MVC, mod_rewrite o framework
non sapevo neanche che volessero dire. 

Poi è venuta la separazione del modello da vista e controller con un componente che si occupava solo di tirare fuori
i record dal DB, degli array associativi.

Poi è venuto fuori che il codice doveva essere agnostico rispetto al database che si usava per memorizzare i dati:
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


---

### Concetti

- Saltiamo tutta la storia
- Il Repository e il Provider
- Il vantaggio della domain query rispetto a le generic query
- Immutabilità
- IDE friendly
- AbstractQuery e Traits ma anche no
