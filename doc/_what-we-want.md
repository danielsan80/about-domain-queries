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
