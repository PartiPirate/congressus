# Démocratie Liquide

## Explication sur le code et son fonctionnement

### `DelegationBo`

Classe qui permet de calculer les délégations finales à partir d'un jeu de délégations et d'un contexte

#### Délégations

Un jeu de délégation : 

- Espace de décision (groupe de personnes défini)
- Un graphe orienté de points de pouvoir entre deux personnes (définition des arêtes) avec des libellés et des conditions

#### Contexte

- Espace de décision (une réunion)
- Motion qui est votée (possiblement voir un objet moins complexe) : `$context["motion"]`
- Votants : `$context["voters"]`, `$context["me"]`

#### Méthodes 

##### `computeFixationWithContext`

Méthode permettant le calcul des délégations avec contexte en utilisant le moteur conditionnel.

Trois paramètres : 

- instance : instance des électeurs qui prennent part au vote
- motion : motion qui est votée, une partie du contexte
- votes : un tableau de vote, permet de savoir qui a voté, deuxième partie du contexte

###### Initialisation

L'instance nous permet d'avoir trois élements essentiels : 

- Le maximum initial de pouvoirs de vote d'un membre de cette instance
- Les éligibles, sans pouvoir de vote initialement
- Les votants, avec le pouvoir maximal initialement

Une personne peut être membre des deux collèges éligibles ou votants, dans ce cas, elle possède tout son pouvoir de vote initial.

Une passe de nettoyage des données des membres est effectuée

On va chercher les délégations AVEC conditions

###### Nettoyage des délégations inutiles

Sur l'ensemble des délégations on applique les choses suivantes : 

- On cherche les membres pour lesquels :
    - la délégation s'applique
    - la délégation leur appartient
- On indique dans le contexte courant que la personne courante sur laquelle s'applique le moteur conditionnel est le propriétaire de la délégation
- S'il y a une condition, on décode la condition, on appplique le moteur conditionnel sur cette condition sur le contexte
- Si le propriétaire de la délégation ou la personne sur laquelle elle s'applique ne sont pas trouvées ou bien que la condition n'est pas réalisée, alors la délégation est mise de côtée et ne sera pas appliquée dans la suite du calcul.

###### Réarrangement des délégations

On range les délégations par membre puis selon leur ordre intrinsèque

###### Le nettoyage des délégations dans leur ordre d'application avec fin de délégation possible

Sur cette passe on fait les choses suivantes : 

- On part d'un index initial
- On récupère à partir de cet index l'ensemble des délégations du membre de notre première délégation
- On vérifie si le membre a conditionné préalablement une fin de délégation
    - Pour chaque délégation on vérifie qu'il y a assez de pouvoir à distribuer pour que celle ci soit effective, s'il n'en reste pas assez, le reliquat est appliquat, s'il n'y en a plus, la délégation est écartée.
    - S'il y a une fin de délégation signalée dans cette délégation, alors la fin délégation est positionnée
- On saute autant de délégations que nécessaire dans l'ensemble des délégations pour refaire le processeus

###### 

On initialise le facteur de dilution / érosion, qui correspond au pourcentage de pouvoir conservé véritablement apres chaque délégation.

Tant qu'on a des délégations : 

- Pour l'ensemble des délégations on détermine l'ensemble des personnes qui donnent (givers) et qui reçoivent (takers) des pouvoirs de votes par l'intermédiaire d'une délégation
- Tout personne qui reçoit du pouvoir de vote (un `taker`) n'est pas prise en compte sur cette passe et est donc retirée des `givers`
- Sur l'ensemble des `givers` restants
    - On cherche la personne `giver` concernée
    - On cherche sur l'ensemble des délégations, ses délégations
    - On cherche sur chacune des délégations la personne qui reçoit `taker`
    - On calcul le pouvoir que le `giver` va donner en fonction de la délégation, ainsi que le pouvoir 'dilué' qui va être reçu par le `taker`. Une absence d'érosion du pouvoir permet de passer l'intégralité des points de pouvoirs entre un `giver` et un `taker`.
    - On ajoute au `taker` ce pouvoir de vote, on calcule son max de pouvoir de vote, on indique au `giver` le nombre de point qu'il a donné et on l'ajoute à l'ensemble des `givers` du `taker` pour construire l'arborescence des `givers` niveau par niveau.
    - On réajuste le niveau du `taker` en fonction du niveau le plus haut de ses `givers`
- On réarrange les personnes en fonction de leur pouvoir final de vote

##### `computeFixation`

Méthode permettant le calcul des délégations sans contexte.

### Moteur conditionnel

#### `ConditionalFactory`

Classe Factory point d'entrée du moteur conditionnel avec trois méthodes statiques : 

- getConditionInstance($condition) : récupère une classe de type ICondition
- getOperatorInstance($condition) : récupère une classe de type IOperator
- testConditions($conditions, $context) : tests un jeu de condition avec le contexte

##### Structure sous-jacentence

```
├── ConditionalFactory
├── ICondition
│   ├── MotionDateCondition
│   ├── MotionDescriptionCondition
│   ├── MotionTagsCondition
│   ├── MotionTitleCondition
│   └── VoterMeCondition
└── IOperator
    ├── ContainsOperator
    ├── DoNotContainOperator
    ├── DoVoteOperator
    ├── EqualsOperator
    ├── IsAfterOperator
    └── IsBeforeOperator
```

##### testConditions

Methode mettant en application l'algorithme de test des conditions :

- Première boucle, sur l'ensemble des conditions, dont le but est de faire deux choses 
    - Evaluer la condition sur le contexte (méthode `evaluateCondition` de la classe ICondition)
    - Création des groupes conditionnels sur les mots clés (interaction) `if`, `andif` et `orif``

```
Si le titre de la motion contient A
Et le titre de la motion contient B
Ou si le titre de la motion contient C
Et le titre de la motion contient D
```
==> Définit deux groupes conditionnels

```
Si le titre de la motion contient A
Et le titre de la motion contient B
```
ou
```
Si le titre de la motion contient C
Et le titre de la motion contient D
```

Le moteur conditionnel *in fine* ne contient que deux niveaux : 

- Un niveaux de groupes de conditions articulés entre eux
- Chaque groupe contenants des conditions articulées entre elles

Le moteur conditionnel est linéaire de gauche à droite

- Deuxième boucle sur l'ensemble des groupes conditionnels 
    - Lecture de gauche à droite sans priorité du `et` sur le `ou` des conditions dans chacun des groupes de conditions et détermination d'un booléen global
    - Lecture de gauche à droite sans priorité du `et` sur le `ou` des résultats des conditions pour calcul d'un booléen final

Un groupe de condition en 

```
Si ... A
Et ... B
Ou ... C
Et ... D
```

Sera résolu comme (( A et B ) ou C ) et D

Des groupe de conditions en 

```
Si ... A
Et si ... B
Ou si  ... C
Et si ... D
```

Sera résolu comme (( A et B ) ou C ) et D

#### Les conditions 

##### `MotionDateCondition`

On cherche dans le contexte la motion et sa date de mise au vote qui est ensuite soumise à l'opérateur de test avec la valeur de comparaison fournit par la condition

##### `MotionDescriptionCondition`

On cherche dans le contexte la motion et sa description qui est ensuite soumise à l'opérateur de test avec la valeur de comparaison fournit par la condition.
Le test est opéré sur la valeur "explosée" (tableau de sous-valeurs) par une virgule.

##### `MotionTagsCondition`

On cherche dans le contexte la motion et l'ensemble des tags qui lui est appliqué. Pour chacun des tags, jusqu'à réponse positive, l'opérateur de test est opéré sur la condition et sa valeur de comparaison.

##### `MotionTitleCondition`

On cherche dans le contexte la motion et son titre qui est ensuite soumis à l'opérateur de test avec la valeur de comparaison fournit par la condition.
Le test est opéré sur la valeur "explosée" (tableau de sous-valeurs) par une virgule.

##### `VoterMeCondition`

On cherche dans le contexte le votant sur lequel est appliqué la condition. Le test de l'opérateur est opéré sur le contexte entier.

#### Les opérateurs

##### `ContainsOperator`

L'opérateur reçoit une valeur de texte en première opérande (`$value`) qui est comparé à la valeur deuxième opérande (`$compareTo`). Le test est insible à la casse. Le test est positif si la première opérande contient la deuxième. Si l'une des deux opérandes est vide, alors le test sera négatif.

##### `DoNotContainOperator`

L'opérateur reçoit une valeur de texte en première opérande (`$value`) qui est comparé à la valeur deuxième opérande (`$compareTo`). Le test est insible à la casse. Le test est positif si la première opérande ne contient pas la deuxième. Si l'une des deux opérandes est vide, alors le test sera négatif.

##### `DoVoteOperator`

L'opérateur reçoit une personne en première opérande (`$value`) qui est comparé à l'ensemble des votants fourni par le contexte. La deuxième opérande n'a aucun aucun impact dans le test

##### `EqualsOperator`

L'opérateur reçoit une valeur de texte en première opérande (`$value`) qui est comparé à la valeur deuxième opérande (`$compareTo`). Le test est insible à la casse. Le test est positif si la première opérande est égale la deuxième. Si l'une des deux opérandes est vide, alors le test sera négatif.

##### `IsAfterOperator`

L'opérateur reçoit une date au format iso AAAA-MM-JJ (`$value`) qui est comparé à une autre date au format iso fournit par la deuxième opérande (`$compareTo`). Si la date testée est situé après (ou à la même date) alors le test est positif (`true`).

##### `IsBeforeOperator`

L'opérateur reçoit une date au format iso AAAA-MM-JJ (`$value`) qui est comparé à une autre date au format iso fournit par la deuxième opérande (`$compareTo`). Si la date testée est situé avant (ou à la même date) alors le test est positif (`true`).
