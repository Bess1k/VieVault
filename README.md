# VieVault

VieVault est un coffre-fort numérique qui permet de stocker des informations comme des mots de passe, des documents, des codes ou des cryptomonnaies. L'application permet de désigner des bénéficiaires qui pourront récupérer ces données après le décès de l'utilisateur, après validation par un notaire.

Ce projet a été réalisé dans le cadre de la formation DWWM au CCI Campus Colmar, en avril 2026.

L'application est développée avec Symfony 8 et utilise PostgreSQL comme base de données. Le contenu des éléments stockés dans le coffre est chiffré avant d'être enregistré. Un système de mot de passe panique permet à l'utilisateur d'afficher un faux coffre en cas de contrainte, tout en envoyant une alerte à un contact de confiance.

Pour installer le projet, il faut d'abord cloner le dépôt, puis lancer composer install pour récupérer les dépendances. Ensuite il faut copier le fichier .env en .env.local et y configurer la variable DATABASE_URL avec les identifiants de la base de données PostgreSQL, ainsi que MAILER_DSN pour l'envoi des emails.

Une fois la configuration terminée, on crée la base avec la commande symfony console doctrine:database:create, puis on exécute les migrations avec symfony console doctrine:migrations:migrate. 


Auteur : Besnik MEHMETI
