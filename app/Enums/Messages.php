<?php

namespace App\Enums;

enum Messages: string
{
    // Auth Messages
    case MAGIC_LINK_SENT = 'Lien magique envoyé';
    case INVALID_PHONE = 'Numéro de téléphone invalide';
    case LINK_EXPIRED = 'Lien expiré ou invalide';
    case INVALID_PIN = 'Code PIN invalide';
    case LOGIN_SUCCESS = 'Connexion réussie';
    case TELEPHONE_NOT_FOUND = 'Numéro de téléphone non trouvé';
    case MARCHANT_NOT_FOUND = 'Marchant non trouvé';
    case TOKEN_EXPIRED_INVALID = 'Token expiré ou invalide';
    case PIN_INVALID = 'PIN invalide';
    case ACCESS_DENIED = 'Accès non autorisé';
    case COMPTE_CREATED = 'Compte créé avec succès';
    case COMPTE_NOT_FOUND = 'Compte non trouvé';
    case INVALID_TOKEN = 'Token invalide ou expiré';

    // Validation Messages
    case TELEPHONE_REQUIRED = 'Le numéro de téléphone est obligatoire';
    case TELEPHONE_STRING = 'Le numéro de téléphone doit être une chaîne de caractères';
    case TELEPHONE_REGEX = 'Le numéro de téléphone doit être au format +221XXXXXXXXX';
    case TELEPHONE_UNIQUE = 'Ce numéro de téléphone est déjà utilisé';
    case PIN_REQUIRED = 'Le code PIN est obligatoire';
    case PIN_STRING = 'Le code PIN doit être une chaîne de caractères';
    case PIN_DIGITS = 'Le code PIN doit contenir exactement 4 chiffres';
    case PIN_CONFIRMED = 'La confirmation du code PIN ne correspond pas';
    case TOKEN_REQUIRED = 'Le token est obligatoire';
    case TOKEN_STRING = 'Le token doit être une chaîne de caractères';
    case COMPTE_ID_REQUIRED = 'L\'ID du compte est obligatoire';
    case COMPTE_ID_UUID = 'L\'ID du compte doit être un UUID valide';
    case COMPTE_ID_EXISTS = 'Le compte spécifié n\'existe pas';
    case RECEIVER_COMPTE_ID_DIFFERENT = 'Le compte destinataire doit être différent du compte expéditeur';
    case RECEIVER_COMPTE_ID_EXISTS = 'Le compte destinataire spécifié n\'existe pas';
    case MONTANT_REQUIRED = 'Le montant est obligatoire';
    case MONTANT_NUMERIC = 'Le montant doit être un nombre';
    case MONTANT_MIN_100 = 'Le montant minimum est de 100 FCFA';
    case MONTANT_MAX_1000 = 'Le montant maximum est de 1 000 FCFA';
    case MONTANT_MAX_10000 = 'Le montant maximum est de 10 000 FCFA';
    case MONTANT_MAX_100000 = 'Le montant maximum est de 100 000 FCFA';
    case MONTANT_MAX_1000000 = 'Le montant maximum est de 1 000 000 FCFA';
    case PARTENAIRE_ID_REQUIRED = 'L\'ID du partenaire est obligatoire';
    case PARTENAIRE_ID_UUID = 'L\'ID du partenaire doit être un UUID valide';
    case PARTENAIRE_ID_EXISTS = 'Le partenaire spécifié n\'existe pas';
    case QR_DATA_REQUIRED = 'Les données QR sont obligatoires';
    case QR_DATA_STRING = 'Les données QR doivent être une chaîne de caractères';
    case NAME_REQUIRED = 'Le nom est obligatoire';
    case NAME_MAX_255 = 'Le nom ne doit pas dépasser 255 caractères';

    case INVALIDE_DATA = 'Données de validation invalides';

    // Marchant Messages
    case MARCHANT_NOM_BOUTIQUE_REQUIRED = 'Le nom de la boutique est requis';
    case MARCHANT_NOM_BOUTIQUE_STRING = 'Le nom de la boutique doit être une chaîne de caractères';
    case MARCHANT_NOM_BOUTIQUE_MAX_255 = 'Le nom de la boutique ne peut pas dépasser 255 caractères';
    case MARCHANT_NOM_BOUTIQUE_UNIQUE = 'Ce nom de boutique est déjà utilisé';
    case MARCHANT_ADRESSE_REQUIRED = 'L\'adresse est requise';
    case MARCHANT_ADRESSE_STRING = 'L\'adresse doit être une chaîne de caractères';
    case MARCHANT_ADRESSE_MAX_500 = 'L\'adresse ne peut pas dépasser 500 caractères';
    case MARCHANT_VILLE_REQUIRED = 'La ville est requise';
    case MARCHANT_VILLE_STRING = 'La ville doit être une chaîne de caractères';
    case MARCHANT_VILLE_MAX_100 = 'La ville ne peut pas dépasser 100 caractères';
    case MARCHANT_TELEPHONE_SERVICE_REQUIRED = 'Le numéro de téléphone est requis';
    case MARCHANT_EMAIL_SERVICE_EMAIL = 'L\'adresse email doit être valide';
    case MARCHANT_EMAIL_SERVICE_MAX_255 = 'L\'adresse email ne peut pas dépasser 255 caractères';
    case MARCHANT_EMAIL_SERVICE_UNIQUE = 'Cette adresse email est déjà utilisée';

    // Achat Messages
    case ACHAT_SENDER_COMPTE_ID_REQUIRED = 'L\'ID du compte expéditeur est requis';
    case ACHAT_SENDER_COMPTE_ID_EXISTS = 'Le compte expéditeur n\'existe pas';
    case ACHAT_CODE_MARCHANT_REQUIRED = 'Le code marchand est requis.';
    case ACHAT_CODE_MARCHANT_REGEX = 'Le code marchand doit être au format MCH-XXXXXX .';
    case ACHAT_MONTANT_REQUIRED = 'Le montant est requis';
    case ACHAT_MONTANT_MIN = 'Le montant doit être supérieur à 0';
    case ACHAT_MONTANT_MAX_1000000 = 'Le montant ne peut pas dépasser 1 000 000 FCFA';

    // Marchant Auth Messages
    case MARCHANT_CODE_REQUIRED = 'Le code marchand est requis';
    case MARCHANT_CODE_STRING = 'Le code marchand doit être une chaîne de caractères';
    case MARCHANT_CODE_REGEX = 'Le code marchand doit être au format MCH-XXXXXX';
    case MARCHANT_PIN_REQUIRED = 'Le PIN marchand est requis';
    case MARCHANT_PIN_STRING = 'Le PIN doit être une chaîne de caractères';
    case MARCHANT_PIN_DIGITS = 'Le PIN doit contenir exactement 4 chiffres';

    // Transaction Messages
    case TRANSACTION_TYPE_REQUIRED = 'Le type de transaction est obligatoire';
    case TRANSACTION_TYPE_IN = 'Le type de transaction doit être depot, retrait ou transfert';
    case TRANSACTION_PARTENAIRE_ID_REQUIRED = 'L\'ID du partenaire est obligatoire pour les retraits';
    case TRANSACTION_SENDER_COMPTE_ID_REQUIRED = 'L\'ID du compte expéditeur est obligatoire';
    case TRANSACTION_RECEIVER_COMPTE_ID_REQUIRED = 'L\'ID du compte destinataire est obligatoire';
    case TRANSACTION_MONTANT_MIN_100_XOF = 'Le montant minimum est de 100 XOF';
    case TRANSACTION_MONTANT_MAX_EXCEEDED = 'Le montant maximum autorisé est dépassé';

    // Auth Messages
    case EMAIL_REQUIRED = 'L\'email est obligatoire';
    case EMAIL_EMAIL = 'L\'email doit être valide';
    case PASSWORD_REQUIRED = 'Le mot de passe est obligatoire';
    case PASSWORD_MIN_8 = 'Le mot de passe doit contenir au moins 8 caractères';

    case OPEN_LINK = "Ouvre ce lien pour te connecter à ton compte marchant : ";
    case SOLDE_RECEIVED= 'Solde récupéré avec succès';
}
