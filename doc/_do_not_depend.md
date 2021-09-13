
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

