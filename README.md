# MTGEvents
App for generating pods for multiplayer commander format in Magic:The Gathering

Author and codemaster: Anton "Zinton" Tonych (zinton#5778 in discord)

Admin: Sergey "Next_rim" Orlov (Next_rim#2286 in discord)

The app allows to enter the list of players, and subsequently generate 4-man pods round-by-round. 

The list of players is manually entered by the tournament admin, then 1st round pods are generated. 

The tournament admin then enters the results: score and order of death (1-4, with 4 being the winner). 3 points for win, 1 point for draw, 0 points for loss. Subsequent pods are filled by standings with tiebreak support. The standings depend on personal score>personal result vs other tiebreak players>cummulative order of death. If there is a draw, the order of death = turn order. 

The UI is in Russian, needs translation for international use
