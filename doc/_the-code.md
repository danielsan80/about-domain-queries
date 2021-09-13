## Il Codice

In `/project/src` possiamo trovare il codice sorgente di esempio mentre in `/project/tests` il codice di test.

In `Dan\Daneel\Node\Domain` c'è il codice di dominio del contesto `Node` della versione `Daneel`.

`Dan` è il namespace radice che rappresenta il vendor.

Proseguendo, in `**\Domain\Node` troviamo il codice del sottocontesto `Node` e in particolare in `**\Domain\Model`
c'è tutto il codice relativo al modello del `Node`.

La classe `**\Domain\Node\Model\Node` è la nostra entità `Node` di esempio che ha le caratteristiche
descritte [qui](the-domain.md).

L'interfaccia `**\Domain\Node\Model\Repository\NodeRepository` non verrà usata in questo esempio
poiché ci concentreremo sulla lettura, per cui non vi è alcuna sua implementazione. In realtà non è necessario avere
sempre un repository se il nostro modulo prevede solo la lettura.

L'interfaccia `**\Domain\Node\Model\Provider\NodeProvider` ha appena 3 metodi, 2 per ottenere
esattamente un `node` e solo un altro per ottenere una sottocollezione di `Node`.
Quest'ultimo metodo come avrete intuito è quello che ci interessa maggiormente per l'argomento trattato.

Abbiamo due value object `**\Domain\Node\Model\Code\Code` e `**\Domain\Node\Model\Id\NodeId`
da cui dipende `Node`, il primo per vincolare il `code` del `Node` a rispettare alcune regole e il secondo per definire
l'`id` del `Node` al fine di vincolarlo a contenere uno `uuid`, e soprattutto per avere un riferimento
fortemente tipizzato.

Potrebbe lasciare un po' perplessi il namespace `**\Domain\Node\Model\Provider\Infrastructure` perché
non ci aspettavamo di trovare la keyword `Infratracture` dentro a `Domain`. In realtà questo è un escamotage per tenere
il codice infrastrutturale molto vicino a quello a cui si riferisce, rendendone più semplice il passaggio dall'uno all'altro
navigando via file system.

In pratica usiamo la keyword `Infrastructure` in `append mode` creando isole di codice infrastrutturale dove serve.
La stessa cosa viene fatta con la keyword `Testing` con la quale creiamo isole di codice utile ai test
(sia in `src` che in `test`, in `src` se vuoi permettere a chi dipende dal componente di usare le classi di test).

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

