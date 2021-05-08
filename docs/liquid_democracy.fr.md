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
