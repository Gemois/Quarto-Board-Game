
# QUARTO  BOARD GAME PROJECT
Αυτό το  project υλοποιήθηκε στα πλαίσια του μαθήματος Ανάπτυξη Διαδικτυακών  
Συστημάτων και Εφαρμογών του Τμήματος Μηχανικών Πληροφορικής και Ηλεκτρονικών Συστημάτων ΔΙΠΑΕ
# Demo Page
Μπορείτε να  επισκεφτείτε την σελίδα
https://users.iee.ihu.gr/~it174881/ADISE21_174881/
και να παίξετε την τελευταία έκδοση του παιχνιδιού.<br>
Σημείωση : πρέπει να πατήσετε το start button πριν κάνετε login στο παιχνίδι για να ξεκινήσει.<br>
Προσοχή : το start button πρέπει να πατηθεί μόνο μια φορά και μόνο από έναν browser, στην συνέχεια μπορούν να συνδεθούν οι παίκτες.
# Εγκατάσταση

## Απαιτήσεις

* Apache2
* Mysql Server
* php
## Οδηγίες Εγκατάστασης

 * Κάντε clone το project σε κάποιον φάκελο 
  `$ git clone https://github.com/iee-ihu-gr-course1941/ADISE21_174881.git`

 * Βεβαιωθείτε ότι ο φάκελος είναι προσβάσιμος από τον Apache Server. πιθανόν να χρειαστεί να καθορίσετε τις παρακάτω ρυθμίσεις.

 * Θα πρέπει να δημιουργήσετε στην Mysql την βάση με όνομα 'adise_quarto_db' και να φορτώσετε σε αυτήν την βάση τα δεδομένα από το αρχείο schema.sql

 * Θα πρέπει να φτιάξετε το αρχείο lib/config_local.php το οποίο να περιέχει:
```
    <?php
	$DB_PASS = 'κωδικός';
	$DB_USER = 'όνομα χρήστη';
    ?>
```
# Περιγραφή Παιχνιδιού


Το Quarto είναι ένα επιτραπέζιο παιχνίδι για δύο παίκτες που εφευρέθηκε από τον Ελβετό μαθηματικό Blaise Müller.

Το παιχνίδι παίζεται σε ταμπλό 4×4.
 Υπάρχουν 16 μοναδικά κομμάτια για να παίξετε, καθένα από τα οποία είναι είτε:
```
 - ψηλό ή κοντό
 - άσπρο  ή καφέ (ή ένα διαφορετικό ζευγάρι χρωμάτων).
 - τετράγωνο ή κυκλικό 
 - με βαθουλωτή κορυφή ή συμπαγής κορυφή.
 ```
Οι παίκτες διαλέγουν εκ περιτροπής ένα κομμάτι το οποίο πρέπει να τοποθετήσει ο άλλος παίκτης στο ταμπλό. Ένας παίκτης κερδίζει τοποθετώντας ένα κομμάτι στον πίνακα που σχηματίζει μια οριζόντια, κάθετη ή διαγώνια σειρά τεσσάρων κομματιών, τα οποία έχουν όλα μια κοινή ιδιότητα (όλα κοντά, όλα κυκλικά κ.λπ.).



## Συντελεστές
 Μωυσίδης Γέωργιος 174881  : Jquery , PHP API ,  Σχεδιασμός mysql

# Περιγραφή API

## Methods


### Board
#### Ανάγνωση Board

```
GET /board/
```

Επιστρέφει το [Board](#Board).

#### Αρχικοποίηση Board
```
POST /board/
```

Αρχικοποιεί το Board, δηλαδή το παιχνίδι. Γίνονται reset τα πάντα σε σχέση με το παιχνίδι.
Επιστρέφει το [Board](#Board).

### Piece
#### 'Επιλογή Πιονιού Για  Τον Άλλο Παίκτη

```
PUT /board/piece/pick/
```
Json Data:

| Field             | Description                 | Required   |
| ----------------- | --------------------------- | ---------- |
| `piece_id`        | μοναδικος αριθμος Πιονιού   | yes        |

Επιλέγει το πιόνι που πρέπει να παίκτη από τον άλλο παίκτη
Ενημερώνει το game status για το current_piece.

#### Επιστροφή αχρησιμοποίητων πιονιών 

```
GET /board/piece/pick
```
Επιστρέφει τα στοιχεία από το [Pieces](#Pieces) με pieces_id.
Ώστε να ενημερώνει για τα πιόνια που μπορούν να παιχτούν.

#### Τοποθέτηση πιονιού στο board

```
PUT /board/piece/place/
```
Json Data:

| Field             | Description                 | Required   |
| ----------------- | --------------------------- | ---------- |
| `x`        | Η  θέση x  στο board               | yes        |
| `y`        | Η  θέση y  στο board               | yes        |
| `piece_id` | Μοναδικός αριθμός Πιονιού          | yes        |



### Player

#### Ανάγνωση στοιχείων παικτών
```
GET /players/
```
Επιστρέφει τα στοιχεία των παίκτων του παιχνιδιού.




#### Ανάγνωση στοιχείων παίκτη
```
GET /players/login/
```
Επιστρέφει τα στοιχεία του παίκτη .


#### Καθορισμός στοιχείων παίκτη
```
PUT /players/login/
```
Json Data:

| Field             | Description                 | Required   |
| ----------------- | --------------------------- | ---------- |
| `username`        | Το username για τον παίκτη . | yes        |


Επιστρέφει τα στοιχεία του παίκτη και ένα token. Το token πρέπει να το χρησιμοποιεί ο παίκτης καθόλη τη διάρκεια του παιχνιδιού.

### Status

#### Ανάγνωση κατάστασης παιχνιδιού
```
GET /status/
```

Επιστρέφει το στοιχείο [Game_status](#Game_status).


## Entities


### Board
---------

Το board είναι ένας πίνακας, ο οποίος στο κάθε στοιχείο έχει τα παρακάτω:

| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | --------------- |
| `x`                      | H συντεταγμένη x του τετραγώνου              | 1..4            |                    
| `y`                      | H συντεταγμένη y του τετραγώνου              | 1..4            |                   
| `piece`                  | To Πιόνι που υπάρχει στο τετράγωνο           | 1...16, null    | 


### Pieces
---------

To κάθε κομμάτι έχει τα παρακάτω στοιχεία:

| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | ----------------- |
| `pieces_id` 			   | Μοναδικός  αριθμός						  | 1...16            |
| `is_light`               | Άν είναι λευκό η μαύρο                       | TRUE','FALSE'     |                           
| `is_round`               | Αν είναι στρόγγυλο η τετράγωνο              | TRUE','FALSE'     |
| `is_short`               | Αν είναι κοντό η ψηλό | 'pick','place'       |TRUE','FALSE'      |  
| `is_solid`               | Αν έχει βαθουλωτή κορυφή ή συμπαγής κορυφή   | TRUE','FALSE'     |
| `available`              | Είδος κίνησης που πρέπει να κάνει ο παίκτης  | 'TRUE','FALSE'    |



### Players
---------

O κάθε παίκτης έχει τα παρακάτω στοιχεία:

| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | ---------------------------- |
|`player_id` 			   |Μόναδικος άυξων αριθμος 					  | ΙΝΤ INCREMENT                          |
|`username`                | Όνομα παίκτη                                 | String                       |                        
| `token  `                | To κρυφό token του παίκτη.                   | HEX                          |
| `role`                   | Είδος κίνησης που πρέπει να κάνει ο παίκτης  | 'pick','place'               |





### Game_status
----------------------------

H κατάσταση παιχνιδιού έχει τα παρακάτω στοιχεία:

| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | -----------------------------|
| `status  `               | Κατάσταση  | 'not active', 'initialized', 'started', 'ended', 'aborded'     |
| `p_turn`                 | To token του παίκτη που παίζει                                | HEX         |
|`current_piece`           | Δείχνει το επιλέγμενο πιόνι								   |1...16		 |
| `result`                 | Καθοριστικό νίκης ή ισοπαλίας                                 |'W','D',null |
| `win_direction`          | Καθορίζει την κατεύθυνση νίκης πάνω στο board		   |'not set','vertical+y','horisontal+x','left diagonal','right diagonal' (οπού x και y τελευταίες συντεταγμένες τοποθέτησης|
| `last_change`            | Τελευταία αλλαγή/ενέργεια στην κατάσταση του παιχνιδιού       | timestamp   |

