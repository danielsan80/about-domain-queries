
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

