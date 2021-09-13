
## Il Dominio

In questo post spesso parlo del `Node` e le sue varie declinazioni (`NodeRepository`, `NodeProvider`).

Con `Node` intendo descrivere i nodi di un albero di contenuti gerarchici.

E' un esempio che viene da un problema reale.
Avevamo la necessità di descrivere le proprietà di un generico prodotto.
Altri software utilizzavano un numero di livelli fisso, ad esempio due, Gruppo di proprietà e Proprietà,
ma a noi questa soluzione andava stretta.

Abbiamo pensato che un indefinito numero di livelli ci avrebbe permesso di avere più flessibilità e scalabilità
nella definizione delle proprietà del prodotto e così il Gruppo di proprietà è diventato una Proprietà di tipo Gruppo
con le sue Proprietà figlie.

Anche alcuni CMS utilizzano un approccio simile per memorizzare i contenuti.

Comunque in questo post ho semplificato il nostro problema reale astraendolo nel concetto di `Node`.

Le specifiche di dominio che ho implementato nell'esempio sono queste:

>Abbiamo una collezione di `Node` che rappresentano i nodi di un albero.
>
> Un `Node`
> può avere un `parent` oppure no, in questo secondo caso sarà un nodo radice (e quindi idealmente figlio di
> un ipotetico `RootNode`).
>
> Un `Node` ha 0:n figli, ossia tutti quelli che lo hanno come `parent`.
> 
> Un `Node` senza figli è una foglia
>
> Un `Node` è identificabile oltre che dal proprio `id` anche da un `code` (con alcuni vincoli).
>
> Un `Node` ha una `label` che lo descrive in linguaggio naturale e una `position` per poterlo eventualmente
> ordinare rispetto ai suoi fratelli.