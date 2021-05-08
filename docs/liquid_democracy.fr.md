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
- Motion qui est votée (possiblement voir un objet moins complexe)
- Votants

#### Méthodes 

##### `computeFixation`

Méthode permettant le calcul des conditions sans contexte

##### `computeFixationWithContext`

Méthode permettant le calcul des conditions avec contexte

### Moteur conditionnel

#### `ConditionalFactory`

Classe Factory point d'entrée du moteur conditionnel avec trois méthodes statiques : 

- getConditionInstance($condition) : récupère une classe de type ICondition
- getOperatorInstance($condition) : récupère une classe de type IOperator
- testConditions($conditions, $context) : tests un jeu de condition avec le contexte

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
==> Définitdeux groupes conditionnels

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
