--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: menu_ref; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE menu_ref (
    me_code text NOT NULL,
    me_menu text,
    me_file text,
    me_url text,
    me_description text,
    me_parameter text,
    me_javascript text,
    me_type character varying(2)
);


--
-- Name: COLUMN menu_ref.me_code; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN menu_ref.me_code IS 'Menu Code ';


--
-- Name: COLUMN menu_ref.me_menu; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN menu_ref.me_menu IS 'Label to display';


--
-- Name: COLUMN menu_ref.me_file; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN menu_ref.me_file IS 'if not empty file to include';


--
-- Name: COLUMN menu_ref.me_url; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN menu_ref.me_url IS 'url ';


--
-- Name: COLUMN menu_ref.me_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN menu_ref.me_type IS 'ME for menu
PR for Printing
SP for special meaning (ex: return to line)
PL for plugin';


--
-- Name: profile; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE profile (
    p_name text NOT NULL,
    p_id integer NOT NULL,
    p_desc text,
    with_calc boolean DEFAULT true,
    with_direct_form boolean DEFAULT true
);


--
-- Name: TABLE profile; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE profile IS 'Available profile ';


--
-- Name: COLUMN profile.p_name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile.p_name IS 'Name of the profile';


--
-- Name: COLUMN profile.p_desc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile.p_desc IS 'description of the profile';


--
-- Name: COLUMN profile.with_calc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile.with_calc IS 'show the calculator';


--
-- Name: COLUMN profile.with_direct_form; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile.with_direct_form IS 'show the direct form';


--
-- Name: profile_menu; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE profile_menu (
    pm_id integer NOT NULL,
    me_code text,
    me_code_dep text,
    p_id integer,
    p_order integer,
    p_type_display text NOT NULL,
    pm_default integer
);


--
-- Name: TABLE profile_menu; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE profile_menu IS 'Join  between the profile and the menu ';


--
-- Name: COLUMN profile_menu.me_code_dep; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile_menu.me_code_dep IS 'menu code dependency';


--
-- Name: COLUMN profile_menu.p_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile_menu.p_id IS 'link to profile';


--
-- Name: COLUMN profile_menu.p_order; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile_menu.p_order IS 'order of displaying menu';
COMMENT ON COLUMN profile_menu.pm_default IS 'default menu';


--
-- Name: COLUMN profile_menu.p_type_display; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile_menu.p_type_display IS 'M is a module
E is a menu
S is a select (for plugin)';


--
-- Name: profile_menu_pm_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE profile_menu_pm_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: profile_menu_pm_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE profile_menu_pm_id_seq OWNED BY profile_menu.pm_id;


--
-- Name: profile_menu_pm_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('profile_menu_pm_id_seq', 778, true);


--
-- Name: profile_menu_type; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE profile_menu_type (
    pm_type text NOT NULL,
    pm_desc text
);


--
-- Name: profile_p_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE profile_p_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: profile_p_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE profile_p_id_seq OWNED BY profile.p_id;


--
-- Name: profile_p_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('profile_p_id_seq', 11, true);


--
-- Name: profile_user; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE profile_user (
    user_name text NOT NULL,
    pu_id integer NOT NULL,
    p_id integer
);


--
-- Name: TABLE profile_user; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE profile_user IS 'Contains the available profile for users';


--
-- Name: COLUMN profile_user.user_name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile_user.user_name IS 'fk to available_user : login';


--
-- Name: COLUMN profile_user.p_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN profile_user.p_id IS 'fk to profile';


--
-- Name: profile_user_pu_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE profile_user_pu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: profile_user_pu_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE profile_user_pu_id_seq OWNED BY profile_user.pu_id;


--
-- Name: profile_user_pu_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('profile_user_pu_id_seq', 6, true);


--
-- Name: v_all_menu; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW v_all_menu AS
    SELECT pm.me_code, pm.pm_id, pm.me_code_dep, pm.p_order, pm.p_type_display, pu.user_name, pu.pu_id, p.p_name, p.p_desc, mr.me_menu, mr.me_file, mr.me_url, mr.me_parameter, mr.me_javascript, mr.me_type, pm.p_id, mr.me_description FROM (((profile_menu pm JOIN profile_user pu ON ((pu.p_id = pm.p_id))) JOIN profile p ON ((p.p_id = pm.p_id))) JOIN menu_ref mr USING (me_code)) ORDER BY pm.p_order;


--
-- Name: p_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE profile ALTER COLUMN p_id SET DEFAULT nextval('profile_p_id_seq'::regclass);


--
-- Name: pm_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE profile_menu ALTER COLUMN pm_id SET DEFAULT nextval('profile_menu_pm_id_seq'::regclass);


--
-- Name: pu_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE profile_user ALTER COLUMN pu_id SET DEFAULT nextval('profile_user_pu_id_seq'::regclass);


--
-- Data for Name: menu_ref; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO menu_ref VALUES ('ACH', 'Achat', 'compta_ach.inc.php', NULL, 'Nouvel achat ou dépense', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCHOP', 'Historique', 'anc_history.inc.php', NULL, 'Historique des imputations analytiques', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCGL', 'Grand''Livre', 'anc_great_ledger.inc.php', NULL, 'Grand livre d''plan analytique', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCBS', 'Balance simple', 'anc_balance_simple.inc.php', NULL, 'Balance simple des imputations analytiques', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCBC2', 'Balance croisée double', 'anc_balance_double.inc.php', NULL, 'Balance double croisées des imputations analytiques', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCTAB', 'Tableau', 'anc_acc_table.inc.php', NULL, 'Tableau lié à la comptabilité', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCBCC', 'Balance Analytique/comptabilité', 'anc_acc_balance.inc.php', NULL, 'Lien entre comptabilité et Comptabilité analytique', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCGR', 'Groupe', 'anc_group_balance.inc.php', NULL, 'Balance par groupe', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CSV:AncGrandLivre', 'Impression Grand-Livre', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:AncBalGroup', 'Export Balance groupe analytique', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('OTH:Bilan', 'Export Bilan', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:ledger', 'Export Journaux', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:postedetail', 'Export Poste détail', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:postedetail', 'Export Poste détail', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:fichedetail', 'Export Fiche détail', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('SEARCH', 'Recherche', NULL, NULL, 'Recherche', NULL, 'popup_recherche()', 'ME');
INSERT INTO menu_ref VALUES ('DIVPARM', 'Divers', NULL, NULL, 'Paramètres divers', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGTVA', 'TVA', 'tva.inc.php', NULL, 'Config. de la tva', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CARD', 'Fiche', 'fiche.inc.php', NULL, 'Fiche', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('STOCK', 'Stock', 'stock.inc.php', NULL, 'Stock', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('MOD', 'Menu et profile', NULL, NULL, 'Menu ', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGPRO', 'Profile', 'profile.inc.php', NULL, 'Configuration profile', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGPAY', 'Moyen de paiement', 'payment_middle.inc.php', NULL, 'Config. des méthodes de paiement', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGACC', 'Poste', 'poste.inc.php', NULL, 'Config. poste comptable de base', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('VEN', 'Vente', 'compta_ven.inc.php', NULL, 'Nouvelle vente ou recette', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGMENU', 'Config. Menu', 'menu.inc.php', NULL, 'Configuration des menus et plugins', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('COMPANY', 'Sociétés', 'company.inc.php', NULL, 'Parametre societe', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PERIODE', 'Période', 'periode.inc.php', NULL, 'Gestion des périodes', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PDF:fichedetail', 'Export Fiche détail', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:fiche_balance', 'Export Fiche balance', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:fiche_balance', 'Export Fiche balance', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:report', 'Export report', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:report', 'Export report', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:fiche', 'Export Fiche', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:fiche', 'Export Fiche', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:glcompte', 'Export Grand Livre', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:glcompte', 'Export Grand Livre', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:sec', 'Export Sécurité', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:AncList', 'Export Comptabilité analytique', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:AncBalSimple', 'Export Comptabilité analytique balance simple', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:AncBalSimple', 'Export Comptabilité analytique', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:AncBalDouble', 'Export Comptabilité analytique balance double', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:AncBalDouble', 'Export Comptabilité analytique balance double', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:balance', 'Export Balance comptable', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('PDF:balance', 'Export Balance comptable', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:histo', 'Export Historique', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:ledger', 'Export Journaux', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:AncTable', 'Export Tableau Analytique', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('CSV:AncAccList', 'Export Historique Compt. Analytique', NULL, NULL, NULL, NULL, NULL, 'PR');
INSERT INTO menu_ref VALUES ('SUPPL', 'Fournisseur', 'supplier.inc.php', NULL, 'Suivi fournisseur', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('LET', 'Lettrage', NULL, NULL, 'Lettrage', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCODS', 'Opérations diverses', 'anc_od.inc.php', NULL, 'OD analytique', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('VERIFBIL', 'Vérification ', 'verif_bilan.inc.php', NULL, 'Vérification de la comptabilité', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('REPORT', 'Création de rapport', 'report.inc.php', NULL, 'Création de rapport', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('OPEN', 'Ecriture Ouverture', 'opening.inc.php', NULL, 'Ecriture d''ouverture', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ACHIMP', 'Historique achat', 'history_operation.inc.php', NULL, 'Historique achat', 'ledger_type=ACH', NULL, 'ME');
INSERT INTO menu_ref VALUES ('FOLLOW', 'Courrier', 'action.inc.php', NULL, 'Suivi, courrier, devis', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('FORECAST', 'Prévision', 'forecast.inc.php', NULL, 'Prévision', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('EXT', 'Extension', 'extension_choice.inc.php', NULL, 'Extensions (plugins)', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGDOC', 'Document', 'document_modele.inc.php', NULL, 'Config. modèle de document', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGLED', 'journaux', 'cfgledger.inc.php', NULL, 'Configuration des journaux', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PREDOP', 'Ecriture prédefinie', 'preod.inc.php', NULL, 'Gestion des opérations prédéfinifies', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ADV', 'Avancé', NULL, NULL, 'Menu avancé', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANC', 'Compta Analytique', NULL, NULL, 'Module comptabilité analytique', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGSEC', 'Sécurité', 'param_sec.inc.php', NULL, 'configuration de la sécurité', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PLANANC', 'Plan Compt. analytique', 'anc_pa.inc.php', NULL, 'Plan analytique', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCGROUP', 'Groupe', 'anc_group.inc.php', NULL, 'Groupe analytique', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ODSIMP', 'Historique opérations diverses', 'history_operation.inc.php', NULL, 'Historique opérations diverses', 'ledger_type=ODS', NULL, 'ME');
INSERT INTO menu_ref VALUES ('VENMENU', 'Vente / Recette', NULL, NULL, 'Menu ventes et recettes', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PREFERENCE', 'Préférence', 'pref.inc.php', NULL, 'Préférence', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('HIST', 'Historique', 'history_operation.inc.php', NULL, 'Historique', 'ledger_type=ALL', NULL, 'ME');
INSERT INTO menu_ref VALUES ('MENUFIN', 'Financier', NULL, NULL, 'Menu Financier', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('FIMP', 'Historique financier', 'history_operation.inc.php', NULL, 'Historique financier', 'ledger_type=FIN', NULL, 'ME');
INSERT INTO menu_ref VALUES ('MENUACH', 'Achat', NULL, NULL, 'Menu achat', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('MENUODS', 'Opérations diverses', NULL, NULL, 'Menu opérations diverses', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ODS', 'Opérations Diverses', 'compta_ods.inc.php', NULL, 'Nouvelle opérations diverses', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('FREC', 'Rapprochement', 'compta_fin_rec.inc.php', NULL, 'Rapprochement bancaire', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ADM', 'Administration', 'adm.inc.php', NULL, 'Suivi administration, banque', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('FIN', 'Nouvel extrait', 'compta_fin.inc.php', NULL, 'Nouvel extrait bancaire', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGATCARD', 'Attribut de fiche', 'card_attr.inc.php', NULL, 'Gestion des modèles de fiches', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('FSALDO', 'Soldes', 'compta_fin_saldo.inc.php', NULL, 'Solde des comptes en banques, caisse...', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('JSSEARCH', 'Recherche', NULL, NULL, 'Recherche', NULL, 'search_reconcile()', 'ME');
INSERT INTO menu_ref VALUES ('LETACC', 'Lettrage par Poste', 'lettering.account.inc.php', NULL, 'lettrage par poste comptable', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CARDBAL', 'Balance', 'balance_card.inc.php', NULL, 'Balance par catégorie de fiche', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CUST', 'Client', 'client.inc.php', NULL, 'Suivi client', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGCARDCAT', 'Catégorie de fiche', 'fiche_def.inc.php', NULL, 'Gestion catégorie de fiche', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGCATDOC', 'Catégorie de documents', 'cat_document.inc.php', NULL, 'Config. catégorie de documents', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('VENIMP', 'Historique vente', 'history_operation.inc.php', NULL, 'Historique des ventes', 'ledger_type=VEN', NULL, 'ME');
INSERT INTO menu_ref VALUES ('LETCARD', 'Lettrage par Fiche', 'lettering.card.inc.php', NULL, 'Lettrage par fiche', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('CFGPCMN', 'Plan Comptable', 'param_pcmn.inc.php', NULL, 'Config. du plan comptable', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('LOGOUT', 'Sortie', NULL, 'logout.php', 'Sortie', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('DASHBOARD', 'Tableau de bord', 'dashboard.inc.php', NULL, 'Tableau de bord', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('COMPTA', 'Comptabilité', NULL, NULL, 'Module comptabilité', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('GESTION', 'Gestion', NULL, NULL, 'Module gestion', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PARAM', 'Paramètre', NULL, NULL, 'Module paramètre', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINTJRN', 'Historique', 'impress_jrn.inc.php', NULL, 'Impression historique', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINTREC', 'Rapprochement', 'impress_rec.inc.php', NULL, 'Impression des rapprochements', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINTPOSTE', 'Poste', 'impress_poste.inc.php', NULL, 'Impression du détail d''un poste comptable', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINTREPORT', 'Rapport', 'impress_rapport.inc.php', NULL, 'Impression de rapport', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINTBILAN', 'Bilan', 'impress_bilan.inc.php', NULL, 'Impression de bilan', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINTGL', 'Grand Livre', 'impress_gl_comptes.inc.php', NULL, 'Impression du grand livre', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINTBAL', 'Balance', 'balance.inc.php', NULL, 'Impression des balances comptables', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINTCARD', 'Catégorie de Fiches', 'impress_fiche.inc.php', NULL, 'Impression catégorie de fiches', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('PRINT', 'Impression', NULL, NULL, 'Menu impression', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ACCESS', 'Accueil', NULL, 'user_login.php', 'Accueil', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('ANCIMP', 'Impression', NULL, NULL, 'Impression compta. analytique', NULL, NULL, 'ME');
INSERT INTO menu_ref VALUES ('new_line', 'saut de ligne', NULL, NULL, 'Saut de ligne', NULL, NULL, 'SP');


--
-- Data for Name: profile; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO profile VALUES ('Administrateur', 1, 'Profil par défaut pour les adminstrateurs', true, true);
INSERT INTO profile VALUES ('Utilisateur', 2, 'Profil par défaut pour les utilisateurs', true, true);


--
-- Data for Name: profile_menu; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO profile_menu VALUES (59, 'CFGPAY', 'DIVPARM', 1, 4, 'E', 0);
INSERT INTO profile_menu VALUES (68, 'CFGATCARD', 'DIVPARM', 1, 9, 'E', 0);
INSERT INTO profile_menu VALUES (61, 'CFGACC', 'DIVPARM', 1, 6, 'E', 0);
INSERT INTO profile_menu VALUES (54, 'COMPANY', 'PARAM', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (651, 'ANCHOP', 'ANCIMP', 1, 10, 'E', 0);
INSERT INTO profile_menu VALUES (173, 'COMPTA', NULL, 1, 40, 'M', 0);
INSERT INTO profile_menu VALUES (55, 'PERIODE', 'PARAM', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (56, 'DIVPARM', 'PARAM', 1, 3, 'E', 0);
INSERT INTO profile_menu VALUES (652, 'ANCGL', 'ANCIMP', 1, 20, 'E', 0);
INSERT INTO profile_menu VALUES (60, 'CFGTVA', 'DIVPARM', 1, 5, 'E', 0);
INSERT INTO profile_menu VALUES (653, 'ANCBS', 'ANCIMP', 1, 30, 'E', 0);
INSERT INTO profile_menu VALUES (654, 'ANCBC2', 'ANCIMP', 1, 40, 'E', 0);
INSERT INTO profile_menu VALUES (655, 'ANCTAB', 'ANCIMP', 1, 50, 'E', 0);
INSERT INTO profile_menu VALUES (656, 'ANCBCC', 'ANCIMP', 1, 60, 'E', 0);
INSERT INTO profile_menu VALUES (657, 'ANCGR', 'ANCIMP', 1, 70, 'E', 0);
INSERT INTO profile_menu VALUES (658, 'CSV:AncGrandLivre', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (662, 'new_line', NULL, 1, 35, 'M', 0);
INSERT INTO profile_menu VALUES (67, 'CFGCATDOC', 'DIVPARM', 1, 8, 'E', 0);
INSERT INTO profile_menu VALUES (69, 'CFGPCMN', 'PARAM', 1, 4, 'E', 0);
INSERT INTO profile_menu VALUES (526, 'PRINTGL', 'PRINT', 1, 20, 'E', 0);
INSERT INTO profile_menu VALUES (23, 'LET', 'COMPTA', 1, 8, 'E', 0);
INSERT INTO profile_menu VALUES (523, 'PRINTBAL', 'PRINT', 1, 50, 'E', 0);
INSERT INTO profile_menu VALUES (529, 'PRINTREPORT', 'PRINT', 1, 85, 'E', 0);
INSERT INTO profile_menu VALUES (72, 'PREDOP', 'PARAM', 1, 7, 'E', 0);
INSERT INTO profile_menu VALUES (75, 'PLANANC', 'ANC', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (65, 'CFGCARDCAT', 'DIVPARM', 1, 7, 'E', 0);
INSERT INTO profile_menu VALUES (76, 'ANCODS', 'ANC', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (77, 'ANCGROUP', 'ANC', 1, 3, 'E', 0);
INSERT INTO profile_menu VALUES (78, 'ANCIMP', 'ANC', 1, 4, 'E', 0);
INSERT INTO profile_menu VALUES (45, 'PARAM', NULL, 1, 20, 'M', 0);
INSERT INTO profile_menu VALUES (527, 'PRINTJRN', 'PRINT', 1, 10, 'E', 0);
INSERT INTO profile_menu VALUES (530, 'PRINTREC', 'PRINT', 1, 100, 'E', 0);
INSERT INTO profile_menu VALUES (524, 'PRINTBILAN', 'PRINT', 1, 90, 'E', 0);
INSERT INTO profile_menu VALUES (79, 'PREFERENCE', NULL, 1, 15, 'M', 0);
INSERT INTO profile_menu VALUES (37, 'CUST', 'GESTION', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (38, 'SUPPL', 'GESTION', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (39, 'ADM', 'GESTION', 1, 3, 'E', 0);
INSERT INTO profile_menu VALUES (36, 'CARD', 'GESTION', 1, 6, 'E', 0);
INSERT INTO profile_menu VALUES (40, 'STOCK', 'GESTION', 1, 5, 'E', 0);
INSERT INTO profile_menu VALUES (41, 'FORECAST', 'GESTION', 1, 7, 'E', 0);
INSERT INTO profile_menu VALUES (42, 'FOLLOW', 'GESTION', 1, 8, 'E', 0);
INSERT INTO profile_menu VALUES (29, 'VERIFBIL', 'ADV', 1, 21, 'E', 0);
INSERT INTO profile_menu VALUES (30, 'STOCK', 'ADV', 1, 22, 'E', 0);
INSERT INTO profile_menu VALUES (31, 'PREDOP', 'ADV', 1, 23, 'E', 0);
INSERT INTO profile_menu VALUES (32, 'OPEN', 'ADV', 1, 24, 'E', 0);
INSERT INTO profile_menu VALUES (33, 'REPORT', 'ADV', 1, 25, 'E', 0);
INSERT INTO profile_menu VALUES (5, 'CARD', 'COMPTA', 1, 7, 'E', 0);
INSERT INTO profile_menu VALUES (43, 'HIST', 'COMPTA', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (28, 'ADV', 'COMPTA', 1, 20, 'E', 0);
INSERT INTO profile_menu VALUES (53, 'ACCESS', NULL, 1, 25, 'M', 0);
INSERT INTO profile_menu VALUES (123, 'CSV:histo', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (20, 'LOGOUT', NULL, 1, 30, 'M', 0);
INSERT INTO profile_menu VALUES (35, 'PRINT', 'GESTION', 1, 4, 'E', 0);
INSERT INTO profile_menu VALUES (124, 'CSV:ledger', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (125, 'PDF:ledger', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (6, 'PRINT', 'COMPTA', 1, 6, 'E', 0);
INSERT INTO profile_menu VALUES (126, 'CSV:postedetail', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (3, 'MENUACH', 'COMPTA', 1, 3, 'E', 0);
INSERT INTO profile_menu VALUES (86, 'ACHIMP', 'MENUACH', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (34, 'GESTION', NULL, 1, 45, 'M', 0);
INSERT INTO profile_menu VALUES (18, 'MENUODS', 'COMPTA', 1, 5, 'E', 0);
INSERT INTO profile_menu VALUES (88, 'ODS', 'MENUODS', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (89, 'ODSIMP', 'MENUODS', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (2, 'ANC', NULL, 1, 50, 'M', 0);
INSERT INTO profile_menu VALUES (4, 'VENMENU', 'COMPTA', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (90, 'VEN', 'VENMENU', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (91, 'VENIMP', 'VENMENU', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (19, 'FIN', 'MENUFIN', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (73, 'CFGDOC', 'PARAM', 1, 8, 'E', 0);
INSERT INTO profile_menu VALUES (74, 'CFGLED', 'PARAM', 1, 9, 'E', 0);
INSERT INTO profile_menu VALUES (71, 'CFGSEC', 'PARAM', 1, 6, 'E', 0);
INSERT INTO profile_menu VALUES (82, 'EXT', NULL, 1, 55, 'M', 0);
INSERT INTO profile_menu VALUES (95, 'FREC', 'MENUFIN', 1, 4, 'E', 0);
INSERT INTO profile_menu VALUES (94, 'FSALDO', 'MENUFIN', 1, 3, 'E', 0);
INSERT INTO profile_menu VALUES (27, 'LETACC', 'LET', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (24, 'LETCARD', 'LET', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (167, 'MOD', 'PARAM', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (92, 'MENUFIN', 'COMPTA', 1, 4, 'E', 0);
INSERT INTO profile_menu VALUES (93, 'FIMP', 'MENUFIN', 1, 2, 'E', 0);
INSERT INTO profile_menu VALUES (151, 'SEARCH', NULL, 1, 60, 'M', 0);
INSERT INTO profile_menu VALUES (85, 'ACH', 'MENUACH', 1, 1, 'E', 0);
INSERT INTO profile_menu VALUES (127, 'PDF:postedetail', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (128, 'CSV:fichedetail', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (129, 'PDF:fichedetail', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (130, 'CSV:fiche_balance', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (131, 'PDF:fiche_balance', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (132, 'CSV:report', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (133, 'PDF:report', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (134, 'CSV:fiche', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (135, 'PDF:fiche', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (136, 'CSV:glcompte', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (137, 'PDF:glcompte', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (138, 'PDF:sec', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (139, 'CSV:AncList', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (140, 'CSV:AncBalSimple', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (141, 'PDF:AncBalSimple', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (142, 'CSV:AncBalDouble', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (143, 'PDF:AncBalDouble', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (144, 'CSV:balance', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (145, 'PDF:balance', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (146, 'CSV:AncTable', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (147, 'CSV:AncAccList', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (148, 'CSV:AncBalGroup', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (149, 'OTH:Bilan', NULL, 1, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (528, 'PRINTPOSTE', 'PRINT', 1, 30, 'E', 0);
INSERT INTO profile_menu VALUES (525, 'PRINTCARD', 'PRINT', 1, 40, 'E', 0);
INSERT INTO profile_menu VALUES (1, 'DASHBOARD', NULL, 1, 10, 'M', 1);
INSERT INTO profile_menu VALUES (172, 'CFGPRO', 'MOD', 1, NULL, 'E', 0);
INSERT INTO profile_menu VALUES (171, 'CFGMENU', 'MOD', 1, NULL, 'E', 0);
INSERT INTO profile_menu VALUES (663, 'CFGPAY', 'DIVPARM', 2, 4, 'E', 0);
INSERT INTO profile_menu VALUES (664, 'CFGATCARD', 'DIVPARM', 2, 9, 'E', 0);
INSERT INTO profile_menu VALUES (665, 'CFGACC', 'DIVPARM', 2, 6, 'E', 0);
INSERT INTO profile_menu VALUES (668, 'ANCHOP', 'ANCIMP', 2, 10, 'E', 0);
INSERT INTO profile_menu VALUES (669, 'COMPTA', NULL, 2, 40, 'M', 0);
INSERT INTO profile_menu VALUES (672, 'ANCGL', 'ANCIMP', 2, 20, 'E', 0);
INSERT INTO profile_menu VALUES (673, 'CFGTVA', 'DIVPARM', 2, 5, 'E', 0);
INSERT INTO profile_menu VALUES (674, 'ANCBS', 'ANCIMP', 2, 30, 'E', 0);
INSERT INTO profile_menu VALUES (675, 'ANCBC2', 'ANCIMP', 2, 40, 'E', 0);
INSERT INTO profile_menu VALUES (676, 'ANCTAB', 'ANCIMP', 2, 50, 'E', 0);
INSERT INTO profile_menu VALUES (677, 'ANCBCC', 'ANCIMP', 2, 60, 'E', 0);
INSERT INTO profile_menu VALUES (678, 'ANCGR', 'ANCIMP', 2, 70, 'E', 0);
INSERT INTO profile_menu VALUES (679, 'CSV:AncGrandLivre', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (680, 'new_line', NULL, 2, 35, 'M', 0);
INSERT INTO profile_menu VALUES (681, 'CFGCATDOC', 'DIVPARM', 2, 8, 'E', 0);
INSERT INTO profile_menu VALUES (683, 'PRINTGL', 'PRINT', 2, 20, 'E', 0);
INSERT INTO profile_menu VALUES (684, 'LET', 'COMPTA', 2, 8, 'E', 0);
INSERT INTO profile_menu VALUES (685, 'PRINTBAL', 'PRINT', 2, 50, 'E', 0);
INSERT INTO profile_menu VALUES (686, 'PRINTREPORT', 'PRINT', 2, 85, 'E', 0);
INSERT INTO profile_menu VALUES (688, 'PLANANC', 'ANC', 2, 1, 'E', 0);
INSERT INTO profile_menu VALUES (689, 'CFGCARDCAT', 'DIVPARM', 2, 7, 'E', 0);
INSERT INTO profile_menu VALUES (690, 'ANCODS', 'ANC', 2, 2, 'E', 0);
INSERT INTO profile_menu VALUES (717, 'CSV:ledger', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (718, 'PDF:ledger', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (719, 'PRINT', 'COMPTA', 2, 6, 'E', 0);
INSERT INTO profile_menu VALUES (720, 'CSV:postedetail', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (721, 'MENUACH', 'COMPTA', 2, 3, 'E', 0);
INSERT INTO profile_menu VALUES (722, 'ACHIMP', 'MENUACH', 2, 2, 'E', 0);
INSERT INTO profile_menu VALUES (723, 'GESTION', NULL, 2, 45, 'M', 0);
INSERT INTO profile_menu VALUES (724, 'MENUODS', 'COMPTA', 2, 5, 'E', 0);
INSERT INTO profile_menu VALUES (725, 'ODS', 'MENUODS', 2, 1, 'E', 0);
INSERT INTO profile_menu VALUES (726, 'ODSIMP', 'MENUODS', 2, 2, 'E', 0);
INSERT INTO profile_menu VALUES (727, 'ANC', NULL, 2, 50, 'M', 0);
INSERT INTO profile_menu VALUES (728, 'VENMENU', 'COMPTA', 2, 2, 'E', 0);
INSERT INTO profile_menu VALUES (729, 'VEN', 'VENMENU', 2, 1, 'E', 0);
INSERT INTO profile_menu VALUES (730, 'VENIMP', 'VENMENU', 2, 2, 'E', 0);
INSERT INTO profile_menu VALUES (731, 'FIN', 'MENUFIN', 2, 1, 'E', 0);
INSERT INTO profile_menu VALUES (735, 'EXT', NULL, 2, 55, 'M', 0);
INSERT INTO profile_menu VALUES (736, 'FREC', 'MENUFIN', 2, 4, 'E', 0);
INSERT INTO profile_menu VALUES (737, 'FSALDO', 'MENUFIN', 2, 3, 'E', 0);
INSERT INTO profile_menu VALUES (738, 'LETACC', 'LET', 2, 2, 'E', 0);
INSERT INTO profile_menu VALUES (691, 'ANCGROUP', 'ANC', 2, 3, 'E', 0);
INSERT INTO profile_menu VALUES (692, 'ANCIMP', 'ANC', 2, 4, 'E', 0);
INSERT INTO profile_menu VALUES (694, 'PRINTJRN', 'PRINT', 2, 10, 'E', 0);
INSERT INTO profile_menu VALUES (695, 'PRINTREC', 'PRINT', 2, 100, 'E', 0);
INSERT INTO profile_menu VALUES (696, 'PRINTBILAN', 'PRINT', 2, 90, 'E', 0);
INSERT INTO profile_menu VALUES (697, 'PREFERENCE', NULL, 2, 15, 'M', 0);
INSERT INTO profile_menu VALUES (698, 'CUST', 'GESTION', 2, 1, 'E', 0);
INSERT INTO profile_menu VALUES (699, 'SUPPL', 'GESTION', 2, 2, 'E', 0);
INSERT INTO profile_menu VALUES (700, 'ADM', 'GESTION', 2, 3, 'E', 0);
INSERT INTO profile_menu VALUES (701, 'CARD', 'GESTION', 2, 6, 'E', 0);
INSERT INTO profile_menu VALUES (702, 'STOCK', 'GESTION', 2, 5, 'E', 0);
INSERT INTO profile_menu VALUES (703, 'FORECAST', 'GESTION', 2, 7, 'E', 0);
INSERT INTO profile_menu VALUES (704, 'FOLLOW', 'GESTION', 2, 8, 'E', 0);
INSERT INTO profile_menu VALUES (705, 'VERIFBIL', 'ADV', 2, 21, 'E', 0);
INSERT INTO profile_menu VALUES (706, 'STOCK', 'ADV', 2, 22, 'E', 0);
INSERT INTO profile_menu VALUES (707, 'PREDOP', 'ADV', 2, 23, 'E', 0);
INSERT INTO profile_menu VALUES (708, 'OPEN', 'ADV', 2, 24, 'E', 0);
INSERT INTO profile_menu VALUES (709, 'REPORT', 'ADV', 2, 25, 'E', 0);
INSERT INTO profile_menu VALUES (710, 'CARD', 'COMPTA', 2, 7, 'E', 0);
INSERT INTO profile_menu VALUES (711, 'HIST', 'COMPTA', 2, 1, 'E', 0);
INSERT INTO profile_menu VALUES (712, 'ADV', 'COMPTA', 2, 20, 'E', 0);
INSERT INTO profile_menu VALUES (713, 'ACCESS', NULL, 2, 25, 'M', 0);
INSERT INTO profile_menu VALUES (714, 'CSV:histo', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (715, 'LOGOUT', NULL, 2, 30, 'M', 0);
INSERT INTO profile_menu VALUES (716, 'PRINT', 'GESTION', 2, 4, 'E', 0);
INSERT INTO profile_menu VALUES (739, 'LETCARD', 'LET', 2, 1, 'E', 0);
INSERT INTO profile_menu VALUES (742, 'MENUFIN', 'COMPTA', 2, 4, 'E', 0);
INSERT INTO profile_menu VALUES (743, 'FIMP', 'MENUFIN', 2, 2, 'E', 0);
INSERT INTO profile_menu VALUES (744, 'SEARCH', NULL, 2, 60, 'M', 0);
INSERT INTO profile_menu VALUES (745, 'ACH', 'MENUACH', 2, 1, 'E', 0);
INSERT INTO profile_menu VALUES (746, 'PDF:postedetail', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (747, 'CSV:fichedetail', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (748, 'PDF:fichedetail', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (749, 'CSV:fiche_balance', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (750, 'PDF:fiche_balance', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (751, 'CSV:report', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (752, 'PDF:report', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (753, 'CSV:fiche', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (754, 'PDF:fiche', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (755, 'CSV:glcompte', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (756, 'PDF:glcompte', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (757, 'PDF:sec', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (758, 'CSV:AncList', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (759, 'CSV:AncBalSimple', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (760, 'PDF:AncBalSimple', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (761, 'CSV:AncBalDouble', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (762, 'PDF:AncBalDouble', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (763, 'CSV:balance', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (764, 'PDF:balance', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (765, 'CSV:AncTable', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (766, 'CSV:AncAccList', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (767, 'CSV:AncBalGroup', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (768, 'OTH:Bilan', NULL, 2, NULL, 'P', 0);
INSERT INTO profile_menu VALUES (769, 'PRINTPOSTE', 'PRINT', 2, 30, 'E', 0);
INSERT INTO profile_menu VALUES (770, 'PRINTCARD', 'PRINT', 2, 40, 'E', 0);
INSERT INTO profile_menu VALUES (777, 'CFGPRO', 'MOD', 2, NULL, 'E', 0);
INSERT INTO profile_menu VALUES (778, 'CFGMENU', 'MOD', 2, NULL, 'E', 0);
INSERT INTO profile_menu VALUES (772, 'DASHBOARD', NULL, 2, 10, 'M', 1);


--
-- Data for Name: profile_menu_type; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO profile_menu_type VALUES ('P', 'Impression');
INSERT INTO profile_menu_type VALUES ('S', 'Extension');
INSERT INTO profile_menu_type VALUES ('E', 'Menu');
INSERT INTO profile_menu_type VALUES ('M', 'Module');


--
-- Data for Name: profile_user; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO profile_user VALUES ('phpcompta', 1, 1);


--
-- Name: menu_ref_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY menu_ref
    ADD CONSTRAINT menu_ref_pkey PRIMARY KEY (me_code);


--
-- Name: profile_menu_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY profile_menu
    ADD CONSTRAINT profile_menu_pkey PRIMARY KEY (pm_id);


--
-- Name: profile_menu_type_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY profile_menu_type
    ADD CONSTRAINT profile_menu_type_pkey PRIMARY KEY (pm_type);


--
-- Name: profile_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY profile
    ADD CONSTRAINT profile_pkey PRIMARY KEY (p_id);


--
-- Name: profile_user_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY profile_user
    ADD CONSTRAINT profile_user_pkey PRIMARY KEY (pu_id);


--
-- Name: profile_user_user_name_key; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY profile_user
    ADD CONSTRAINT profile_user_user_name_key UNIQUE (user_name, p_id);


--
-- Name: fki_profile_menu_me_code; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX fki_profile_menu_me_code ON profile_menu USING btree (me_code);


--
-- Name: fki_profile_menu_profile; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX fki_profile_menu_profile ON profile_menu USING btree (p_id);


--
-- Name: fki_profile_menu_type_fkey; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX fki_profile_menu_type_fkey ON profile_menu USING btree (p_type_display);


--
-- Name: profile_menu_me_code_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY profile_menu
    ADD CONSTRAINT profile_menu_me_code_fkey FOREIGN KEY (me_code) REFERENCES menu_ref(me_code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: profile_menu_p_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY profile_menu
    ADD CONSTRAINT profile_menu_p_id_fkey FOREIGN KEY (p_id) REFERENCES profile(p_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: profile_menu_type_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY profile_menu
    ADD CONSTRAINT profile_menu_type_fkey FOREIGN KEY (p_type_display) REFERENCES profile_menu_type(pm_type);


--
-- Name: profile_user_p_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY profile_user
    ADD CONSTRAINT profile_user_p_id_fkey FOREIGN KEY (p_id) REFERENCES profile(p_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

