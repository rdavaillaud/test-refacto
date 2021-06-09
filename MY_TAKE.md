Voici comment j'ai procédé.

Tout d'abord, un tour du propriétaire pour tenter de comprendre ce qu'il s'y passe.  
Une fois compris, j'ai pu en effet voir le legacy smell sur le Manager.

Le principe étant relativement simple, à savoir un remplacement de balise dans un template, mon idée première est de ne pas tout révolutionner, ce qui pourrait amener plus de problèmes collatéraux. Et de factoriser au mieux la gestion des balises à l'aide d'une source de données qui serait envoyé à un système de remplacement de balise.  
Cette source de données serait issue des paramètres en entrée, qui sont de base des entités, facilement évolutive, et non pas du manager lui même, qui ne traitera finalement que la partie remplacement.

Mais avant cela, j'ai d'abord mis en place des tests pour que mes modifications ne fassent pas tomber le fonctionnement actuel.  
En effet, il manquait un certain nombre de test et de cas pour l'existant.

Une petite refactorisation du Manager (méthode pour remplacer les balises) a permis d'homogénéiser le code, facilitant les refactorisations à venir.  
Le déplacement de la génération des sources de données par l'usage d'une interface et d'une méthode `toTemplateDataSource()` permet de vider le Manager de la gestion de la donnée, et donner aux entités la responsabilité de décider ce qui peut être fourni.

Entre temps, quelques tests se sont ajoutés à la liste, pour valider des comportements sous entendus par le code.

Un comportement problématique à mes yeux, était que les balises non utilisées restaient présentes dans le texte final. Dans certains cas plus complexes, cela peut être génant d'envoyer un message avec les balises visibles dans le texte.  
Il y a donc une méthode pour nettoyer les balises non utilisées.

Enfin, pour l'exercice, j'ai rajouté la balise permettant d'afficher la date du devis, simplement en ajoutant la balise dans la méthode `toTemplateDataSource()`. Ainsi, chaque template nécessitant d'afficher la date aura la possibilité de le faire en ajoutant la balise.

Evolutions possibles
----

Je n'ai pas creusé davantage, espérant que ce qui est fait soit déjà satisfaisant.
Le code mériterait un peu plus de commentaires. 

Comme évoqué, je n'ai pas tout changé, partant du principe que le système est plus large que cela, cela doit fonctionner simplement, et proprement, de façon homogène.  
On pourrait imaginer ensuite, par exemple, extraire dans un service la génération des summary text et HTML plutôt que de les laisser dans l'entité, en faisant évoluer le système de templating à qui le rôle de présentation revient.  
Il y a également de fait beaucoup de dépendance au sein de l'entité Quote (SiteRepository et DestinationRepository) qu'il faudrait probablement retirer. 
Ou encore limiter dans le manager la nécessité de connaitre des détails sur la source de données et les préfixes des balises pour réduire si ce n'est éliminer le besoin de le modifier lorsqu'une nouvelle donnée est injectée par exemple.



