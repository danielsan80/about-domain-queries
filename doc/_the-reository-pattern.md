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
che persistono la collezione di `Node` su, e ottengono da, una tecnologia target.

**Gli oggetti di dominio devono essere modellati in modo che le relazioni tra di loro siano gestite
attraverso i loro id.**

Non sarà possibile quindi fare `$node->parent()->code()`.
ma sarà necessario fare `$nodeRepository->byId($node->parentId())->code()`.

In altre parole non vogliamo poter navigare le relazioni come ad esempio è possibile fare con Doctrine.

## CQRS

Siccome ultimamente siamo stati contaminati dalle logiche del CQRS, ha cominciato a suonarci male
l'idea che un repository si occupi sia della scrittura che della lettura degli oggetti in collezione.

Comunque siamo arrivati alla conclusione che l'interfaccia dei Repository andasse spezzata in due:
quella di scrittura e quella di lettura

Forse abbiamo sbagliato ma l'interfaccia per modificare la collezione abbiamo continuato a chiamarla `Repository`
mentre quella per la sola lettura l'abbiamo chiamata `Provider`.

Ad esempio quindi avremo un `NodeRepository` con i metodi per aggiungere e rimuovere i `Node` alla
collezione ed un `NodeProvider` con i metodi per ottenere uno specifico `Node` o un sottoinsieme della collezione
gestita dal repository.
