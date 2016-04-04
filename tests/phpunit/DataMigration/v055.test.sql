--
-- PostgreSQL database dump
--

-- Dumped from database version 9.3.6
-- Dumped by pg_dump version 9.5.0

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

DROP SCHEMA public cascade;
--
-- Name: public; Type: SCHEMA; Schema: -; Owner: api
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO api;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: account; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE account (
    id integer NOT NULL,
    report_id integer,
    bank_name character varying(100) DEFAULT NULL::character varying,
    sort_code character varying(6) DEFAULT NULL::character varying,
    account_number character varying(4) DEFAULT NULL::character varying,
    last_edit timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    created_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    opening_balance numeric(14,2) DEFAULT NULL::numeric,
    opening_date_explanation text,
    closing_balance numeric(14,2) DEFAULT NULL::numeric,
    closing_balance_explanation text,
    opening_date date,
    closing_date date,
    closing_date_explanation text,
    account_type character varying(125) DEFAULT NULL::character varying
);


ALTER TABLE account OWNER TO api;

--
-- Name: account_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE account_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE account_id_seq OWNER TO api;

--
-- Name: account_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE account_id_seq OWNED BY account.id;


--
-- Name: account_transaction; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE account_transaction (
    id integer NOT NULL,
    account_id integer,
    account_transaction_type_id character varying(255) DEFAULT NULL::character varying,
    amount numeric(14,2) DEFAULT NULL::numeric,
    more_details text
);


ALTER TABLE account_transaction OWNER TO api;

--
-- Name: account_transaction_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE account_transaction_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE account_transaction_id_seq OWNER TO api;

--
-- Name: account_transaction_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE account_transaction_id_seq OWNED BY account_transaction.id;


--
-- Name: account_transaction_type; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE account_transaction_type (
    id character varying(255) NOT NULL,
    has_more_details boolean NOT NULL,
    display_order integer,
    type character varying(255) NOT NULL
);


ALTER TABLE account_transaction_type OWNER TO api;

--
-- Name: asset; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE asset (
    id integer NOT NULL,
    report_id integer,
    description text,
    asset_value numeric(14,2) DEFAULT NULL::numeric,
    last_edit timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    title character varying(100) DEFAULT NULL::character varying,
    valuation_date date
);


ALTER TABLE asset OWNER TO api;

--
-- Name: asset_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE asset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE asset_id_seq OWNER TO api;

--
-- Name: asset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE asset_id_seq OWNED BY asset.id;


--
-- Name: audit_log_entry; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE audit_log_entry (
    id integer NOT NULL,
    performed_by_user_id integer,
    user_edited_id integer,
    performed_by_user_name character varying(150) NOT NULL,
    performed_by_user_email character varying(150) NOT NULL,
    ip_address character varying(15) NOT NULL,
    created_at timestamp(0) without time zone NOT NULL,
    action character varying(15) NOT NULL,
    user_edited_name character varying(150) DEFAULT NULL::character varying,
    user_edited_email character varying(150) DEFAULT NULL::character varying
);


ALTER TABLE audit_log_entry OWNER TO api;

--
-- Name: audit_log_entry_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE audit_log_entry_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE audit_log_entry_id_seq OWNER TO api;

--
-- Name: audit_log_entry_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE audit_log_entry_id_seq OWNED BY audit_log_entry.id;


--
-- Name: casrec; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE casrec (
    id integer NOT NULL,
    client_case_number character varying(20) NOT NULL,
    client_lastname character varying(50) NOT NULL,
    deputy_no character varying(100) NOT NULL,
    deputy_lastname character varying(100) DEFAULT NULL::character varying,
    deputy_postcode character varying(10) DEFAULT NULL::character varying
);


ALTER TABLE casrec OWNER TO api;

--
-- Name: casrec_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE casrec_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE casrec_id_seq OWNER TO api;

--
-- Name: casrec_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE casrec_id_seq OWNED BY casrec.id;


--
-- Name: client; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE client (
    id integer NOT NULL,
    case_number character varying(20) DEFAULT NULL::character varying,
    email character varying(60) DEFAULT NULL::character varying,
    phone character varying(20) DEFAULT NULL::character varying,
    address character varying(200) DEFAULT NULL::character varying,
    address2 character varying(200) DEFAULT NULL::character varying,
    county character varying(75) DEFAULT NULL::character varying,
    postcode character varying(10) DEFAULT NULL::character varying,
    country character varying(10) DEFAULT NULL::character varying,
    firstname character varying(50) DEFAULT NULL::character varying,
    lastname character varying(50) DEFAULT NULL::character varying,
    allowed_court_order_types text,
    court_date date,
    last_edit timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE client OWNER TO api;

--
-- Name: COLUMN client.allowed_court_order_types; Type: COMMENT; Schema: public; Owner: api
--

COMMENT ON COLUMN client.allowed_court_order_types IS '(DC2Type:array)';


--
-- Name: client_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE client_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE client_id_seq OWNER TO api;

--
-- Name: client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE client_id_seq OWNED BY client.id;


--
-- Name: contact; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE contact (
    id integer NOT NULL,
    report_id integer,
    contact_name character varying(255) DEFAULT NULL::character varying,
    address character varying(200) DEFAULT NULL::character varying,
    address2 character varying(200) DEFAULT NULL::character varying,
    county character varying(200) DEFAULT NULL::character varying,
    postcode character varying(10) DEFAULT NULL::character varying,
    country character varying(10) DEFAULT NULL::character varying,
    explanation text,
    relationship character varying(100) DEFAULT NULL::character varying,
    phone1 character varying(20) DEFAULT NULL::character varying,
    last_edit timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE contact OWNER TO api;

--
-- Name: contact_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE contact_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE contact_id_seq OWNER TO api;

--
-- Name: contact_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE contact_id_seq OWNED BY contact.id;


--
-- Name: court_order_type; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE court_order_type (
    id integer NOT NULL,
    name character varying(100) DEFAULT NULL::character varying
);


ALTER TABLE court_order_type OWNER TO api;

--
-- Name: court_order_type_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE court_order_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE court_order_type_id_seq OWNER TO api;

--
-- Name: court_order_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE court_order_type_id_seq OWNED BY court_order_type.id;


--
-- Name: dd_user; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE dd_user (
    id integer NOT NULL,
    role_id integer,
    firstname character varying(100) NOT NULL,
    lastname character varying(100) DEFAULT NULL::character varying,
    password character varying(100) NOT NULL,
    email character varying(60) NOT NULL,
    active boolean DEFAULT false,
    salt character varying(100) DEFAULT NULL::character varying,
    registration_date timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    registration_token character varying(100) DEFAULT NULL::character varying,
    email_confirmed boolean,
    token_date timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    address1 character varying(200) DEFAULT NULL::character varying,
    address2 character varying(200) DEFAULT NULL::character varying,
    address3 character varying(200) DEFAULT NULL::character varying,
    address_postcode character varying(10) DEFAULT NULL::character varying,
    address_country character varying(10) DEFAULT NULL::character varying,
    phone_main character varying(20) DEFAULT NULL::character varying,
    phone_alternative character varying(20) DEFAULT NULL::character varying,
    last_logged_in timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    deputy_no character varying(100)
);


ALTER TABLE dd_user OWNER TO api;

--
-- Name: dd_user_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE dd_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dd_user_id_seq OWNER TO api;

--
-- Name: dd_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE dd_user_id_seq OWNED BY dd_user.id;


--
-- Name: decision; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE decision (
    id integer NOT NULL,
    report_id integer,
    description text NOT NULL,
    client_involved_boolean boolean NOT NULL,
    client_involved_details text
);


ALTER TABLE decision OWNER TO api;

--
-- Name: decision_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE decision_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE decision_id_seq OWNER TO api;

--
-- Name: decision_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE decision_id_seq OWNED BY decision.id;


--
-- Name: deputy_case; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE deputy_case (
    client_id integer NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE deputy_case OWNER TO api;

--
-- Name: migrations; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE migrations (
    version character varying(255) NOT NULL
);


ALTER TABLE migrations OWNER TO api;

--
-- Name: report; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE report (
    id integer NOT NULL,
    client_id integer,
    court_order_type_id integer,
    title character varying(150) DEFAULT NULL::character varying,
    start_date date,
    end_date date,
    submit_date timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    last_edit timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    further_information text,
    no_asset_to_add boolean DEFAULT false,
    reason_for_no_contacts text,
    reason_for_no_decisions text,
    submitted boolean,
    reviewed boolean,
    report_seen boolean DEFAULT true NOT NULL,
    all_agreed boolean,
    reason_not_all_agreed text,
    balance_mismatch_explanation text
);


ALTER TABLE report OWNER TO api;

--
-- Name: report_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE report_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE report_id_seq OWNER TO api;

--
-- Name: report_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE report_id_seq OWNED BY report.id;


--
-- Name: role; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE role (
    id integer NOT NULL,
    name character varying(60) NOT NULL,
    role character varying(50) DEFAULT NULL::character varying
);


ALTER TABLE role OWNER TO api;

--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE role_id_seq OWNER TO api;

--
-- Name: role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE role_id_seq OWNED BY role.id;


--
-- Name: safeguarding; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE safeguarding (
    id integer NOT NULL,
    report_id integer,
    do_you_live_with_client character varying(4) DEFAULT NULL::character varying,
    how_often_do_you_visit character varying(55) DEFAULT NULL::character varying,
    how_often_do_you_phone_or_video_call character varying(55) DEFAULT NULL::character varying,
    how_often_do_you_write_email_or_letter character varying(55) DEFAULT NULL::character varying,
    how_often_does_client_see_other_people character varying(55) DEFAULT NULL::character varying,
    anything_else_to_tell text,
    does_client_receive_paid_care text,
    how_is_care_funded character varying(255) DEFAULT NULL::character varying,
    who_is_doing_the_caring text,
    does_client_have_a_care_plan character varying(4) DEFAULT NULL::character varying,
    when_was_care_plan_last_reviewed date,
    how_often_contact_client text
);


ALTER TABLE safeguarding OWNER TO api;

--
-- Name: safeguarding_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE safeguarding_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE safeguarding_id_seq OWNER TO api;

--
-- Name: safeguarding_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE safeguarding_id_seq OWNED BY safeguarding.id;


--
-- Name: transaction; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE transaction (
    id integer NOT NULL,
    report_id integer,
    transaction_type_id character varying(255) DEFAULT NULL::character varying,
    amount numeric(14,2) DEFAULT NULL::numeric,
    more_details text
);


ALTER TABLE transaction OWNER TO api;

--
-- Name: transaction_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE transaction_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE transaction_id_seq OWNER TO api;

--
-- Name: transaction_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE transaction_id_seq OWNED BY transaction.id;


--
-- Name: transaction_type; Type: TABLE; Schema: public; Owner: api
--

CREATE TABLE transaction_type (
    id character varying(255) NOT NULL,
    has_more_details boolean NOT NULL,
    display_order integer,
    category character varying(255) NOT NULL,
    type character varying(255) NOT NULL
);


ALTER TABLE transaction_type OWNER TO api;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY account ALTER COLUMN id SET DEFAULT nextval('account_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY account_transaction ALTER COLUMN id SET DEFAULT nextval('account_transaction_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY asset ALTER COLUMN id SET DEFAULT nextval('asset_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY audit_log_entry ALTER COLUMN id SET DEFAULT nextval('audit_log_entry_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY casrec ALTER COLUMN id SET DEFAULT nextval('casrec_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY client ALTER COLUMN id SET DEFAULT nextval('client_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY contact ALTER COLUMN id SET DEFAULT nextval('contact_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY court_order_type ALTER COLUMN id SET DEFAULT nextval('court_order_type_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY dd_user ALTER COLUMN id SET DEFAULT nextval('dd_user_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY decision ALTER COLUMN id SET DEFAULT nextval('decision_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY report ALTER COLUMN id SET DEFAULT nextval('report_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY role ALTER COLUMN id SET DEFAULT nextval('role_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY safeguarding ALTER COLUMN id SET DEFAULT nextval('safeguarding_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: api
--

ALTER TABLE ONLY transaction ALTER COLUMN id SET DEFAULT nextval('transaction_id_seq'::regclass);


--
-- Data for Name: account; Type: TABLE DATA; Schema: public; Owner: api
--

COPY account (id, report_id, bank_name, sort_code, account_number, last_edit, created_at, opening_balance, opening_date_explanation, closing_balance, closing_balance_explanation, opening_date, closing_date, closing_date_explanation, account_type) FROM stdin;
\.


--
-- Name: account_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('account_id_seq', 1, false);


--
-- Data for Name: account_transaction; Type: TABLE DATA; Schema: public; Owner: api
--

COPY account_transaction (id, account_id, account_transaction_type_id, amount, more_details) FROM stdin;
\.


--
-- Name: account_transaction_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('account_transaction_id_seq', 1, false);


--
-- Data for Name: account_transaction_type; Type: TABLE DATA; Schema: public; Owner: api
--

COPY account_transaction_type (id, has_more_details, display_order, type) FROM stdin;
disability_living_allowance_or_personal_independence_payment	f	1	in
attendance_allowance	f	2	in
employment_support_allowance_or_incapacity_benefit	f	3	in
severe_disablement_allowance	f	4	in
income_support_or_pension_credit	f	5	in
housing_benefit	f	6	in
state_pension	f	7	in
universal_credit	f	8	in
other_benefits_eg_winter_fuel_or_cold_weather_payments	f	9	in
occupational_pensions	f	10	in
account_interest	f	11	in
income_from_investments_property_or_dividends	f	12	in
salary_or_wages	f	13	in
refunds	f	14	in
bequests_eg_inheritance_gifts_received	f	15	in
sale_of_investments_property_or_assets	t	16	in
compensation_or_damages_awards	t	17	in
transfers_in_from_client_s_other_accounts	t	18	in
any_other_money_paid_in_and_not_listed_above	t	19	in
care_fees_or_local_authority_charges_for_care	f	1	out
accommodation_costs_eg_rent_mortgage_service_charges	f	2	out
household_bills_eg_water_gas_electricity_phone_council_tax	f	3	out
day_to_day_living_costs_eg_food_toiletries_clothing_sundries	f	4	out
debt_payments_eg_loans_cards_care_fee_arrears	f	5	out
travel_costs_for_client_eg_bus_train_taxi_fares	f	6	out
holidays_or_day_trips	f	7	out
tax_payable_to_hmrc	f	8	out
insurance_eg_life_home_and_contents	f	9	out
office_of_the_public_guardian_fees	f	10	out
deputy_s_security_bond	f	11	out
client_s_personal_allowance_eg_spending_money	t	12	out
cash_withdrawals	t	13	out
professional_fees_eg_solicitor_or_accountant_fees	t	14	out
deputy_s_expenses	t	15	out
gifts	t	16	out
major_purchases_eg_property_vehicles	t	17	out
property_maintenance_or_improvement	t	18	out
investments_eg_shares_bonds_savings	t	19	out
transfers_out_to_other_client_s_accounts	t	20	out
any_other_money_paid_out_and_not_listed_above	t	21	out
\.


--
-- Data for Name: asset; Type: TABLE DATA; Schema: public; Owner: api
--

COPY asset (id, report_id, description, asset_value, last_edit, title, valuation_date) FROM stdin;
\.


--
-- Name: asset_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('asset_id_seq', 1, false);


--
-- Data for Name: audit_log_entry; Type: TABLE DATA; Schema: public; Owner: api
--

COPY audit_log_entry (id, performed_by_user_id, user_edited_id, performed_by_user_name, performed_by_user_email, ip_address, created_at, action, user_edited_name, user_edited_email) FROM stdin;
\.


--
-- Name: audit_log_entry_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('audit_log_entry_id_seq', 6, true);


--
-- Data for Name: casrec; Type: TABLE DATA; Schema: public; Owner: api
--

COPY casrec (id, client_case_number, client_lastname, deputy_no, deputy_lastname, deputy_postcode) FROM stdin;
\.


--
-- Name: casrec_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('casrec_id_seq', 1, false);


--
-- Data for Name: client; Type: TABLE DATA; Schema: public; Owner: api
--

COPY client (id, case_number, email, phone, address, address2, county, postcode, country, firstname, lastname, allowed_court_order_types, court_date, last_edit) FROM stdin;
1	12345678	\N	12345675432113456	petty france	\N	\N	sw1	AM	TestName	TestSurname	a:2:{i:0;i:2;i:1;i:1;}	2015-01-01	\N
2	12345678	\N	12345675432113456	petty france	\N	\N	sw1	AM	TestName	TestSurname	a:2:{i:0;i:2;i:1;i:1;}	2015-01-01	\N
3	12345678	\N	12345675432113456	petty france	\N	\N	sw1	AM	TestName	TestSurname	a:2:{i:0;i:2;i:1;i:1;}	2015-01-01	\N
4	12345678	\N	12345675432113456	petty france	\N	\N	sw1	AM	TestName	TestSurname	a:2:{i:0;i:2;i:1;i:1;}	2015-01-01	\N
\.


--
-- Name: client_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('client_id_seq', 4, true);


--
-- Data for Name: contact; Type: TABLE DATA; Schema: public; Owner: api
--

COPY contact (id, report_id, contact_name, address, address2, county, postcode, country, explanation, relationship, phone1, last_edit) FROM stdin;
\.


--
-- Name: contact_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('contact_id_seq', 1, false);


--
-- Data for Name: court_order_type; Type: TABLE DATA; Schema: public; Owner: api
--

COPY court_order_type (id, name) FROM stdin;
1	Personal Welfare
2	Property and Affairs
\.


--
-- Name: court_order_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('court_order_type_id_seq', 2, true);


--
-- Data for Name: dd_user; Type: TABLE DATA; Schema: public; Owner: api
--

COPY dd_user (id, role_id, firstname, lastname, password, email, active, salt, registration_date, registration_token, email_confirmed, token_date, address1, address2, address3, address_postcode, address_country, phone_main, phone_alternative, last_logged_in, deputy_no) FROM stdin;
1	5	AD user	AD surname	password	ad@publicguardian.gsi.gov.uk	t	\N	2016-01-25 12:50:25		\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
3	2	Lay Deputy	User	igYBkwGpWwBgl+LmEbwWA7UnwUIWBbfwXd/X42/Pr3Ila/SwO7WorqTdy1PBss82ViGfgN5dXoRA+/7JL5u7bg==	laydeputy@publicguardian.gsi.gov.uk	t	\N	2016-01-25 12:50:25		\N	\N	plat house	lyon road	\N	ha12ex	GB	123456789754	\N	2016-01-25 12:50:37	\N
4	2	ee	cc	password	test@example.org	t	\N	2016-01-25 12:53:15		\N	2016-01-25 12:53:15	plat house	lyon road	\N	ha12ex	GB	123456789754	\N	2016-01-25 12:53:37	\N
5	2	ee	ccc	password	test+2@example.org	t	\N	2016-01-25 12:54:48		\N	2016-01-25 12:54:48	plat house	lyon road	\N	ha12ex	GB	123456789754	\N	2016-01-25 12:55:05	\N
2	1	Admin	User	password	admin@publicguardian.gsi.gov.uk	t	\N	2016-01-25 12:50:25		\N	\N	\N	\N	\N	\N	\N	\N	\N	2016-01-25 13:20:30	\N
6	2	eee	ccc	password	test+3@example.org	t	\N	2016-01-25 13:20:41		\N	2016-01-25 13:20:41	plat house	lyon road	\N	ha12ex	GB	123456789754	\N	2016-01-25 13:20:57	\N
\.


--
-- Name: dd_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('dd_user_id_seq', 6, true);


--
-- Data for Name: decision; Type: TABLE DATA; Schema: public; Owner: api
--

COPY decision (id, report_id, description, client_involved_boolean, client_involved_details) FROM stdin;
\.


--
-- Name: decision_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('decision_id_seq', 1, false);


--
-- Data for Name: deputy_case; Type: TABLE DATA; Schema: public; Owner: api
--

COPY deputy_case (client_id, user_id) FROM stdin;
1	3
2	4
3	5
4	6
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: api
--

COPY migrations (version) FROM stdin;
039
040
041
042
043
044
045
046
047
048
049
050
051
052
053
054
055
\.


--
-- Data for Name: report; Type: TABLE DATA; Schema: public; Owner: api
--

COPY report (id, client_id, court_order_type_id, title, start_date, end_date, submit_date, last_edit, further_information, no_asset_to_add, reason_for_no_contacts, reason_for_no_decisions, submitted, reviewed, report_seen, all_agreed, reason_not_all_agreed, balance_mismatch_explanation) FROM stdin;
1	1	2	\N	2016-01-01	2016-05-01	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N
2	2	2	\N	2015-01-01	2015-01-20	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N
3	3	2	\N	2016-01-01	2016-01-31	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N
4	4	2	\N	2016-01-01	2016-02-01	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N
\.


--
-- Name: report_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('report_id_seq', 4, true);


--
-- Data for Name: role; Type: TABLE DATA; Schema: public; Owner: api
--

COPY role (id, name, role) FROM stdin;
1	OPG Administrator	ROLE_ADMIN
2	Lay Deputy	ROLE_LAY_DEPUTY
3	Professional Deputy	ROLE_PROFESSIONAL_DEPUTY
4	Local Authority Deputy	ROLE_LOCAL_AUTHORITY_DEPUTY
5	Assisted Digital Support	ROLE_AD
\.


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('role_id_seq', 5, true);


--
-- Data for Name: safeguarding; Type: TABLE DATA; Schema: public; Owner: api
--

COPY safeguarding (id, report_id, do_you_live_with_client, how_often_do_you_visit, how_often_do_you_phone_or_video_call, how_often_do_you_write_email_or_letter, how_often_does_client_see_other_people, anything_else_to_tell, does_client_receive_paid_care, how_is_care_funded, who_is_doing_the_caring, does_client_have_a_care_plan, when_was_care_plan_last_reviewed, how_often_contact_client) FROM stdin;
1	2	yes	\N	\N	\N	\N	\N	no	\N	test	no	\N	\N
2	3	no	everyday	once_a_week	once_a_month	more_than_twice_a_year	\N	no	\N	test	no	\N	I (or other deputies) visit TestName Every day\r\nI (or other deputies) phone or video call TestName At least once a week\r\nI (or other deputies) write emails or letters to TestName At least once a month\r\nTestName sees other people More than twice a year\r\nAnything else: -\r\n
3	4	no	less_than_once_a_year	once_a_year	more_than_twice_a_year	once_a_month	first line\r\nsecond line with special chars \r\nexcl mark !\r\nat @\r\nmoney £$\r\npercent %\r\ncaret ^\r\nand &\r\nast *\r\nbrakets () {} []\r\ntags <b>bold</b>\r\nquotes 'single' "double"\r\n\r\nline before and after this are empty	no	\N	test	no	\N	I (or other deputies) visit TestName Less than once a year\r\nI (or other deputies) phone or video call TestName Once a year\r\nI (or other deputies) write emails or letters to TestName More than twice a year\r\nTestName sees other people At least once a month\r\nAnything else: first line\r\nsecond line with special chars \r\nexcl mark !\r\nat @\r\nmoney £$\r\npercent %\r\ncaret ^\r\nand &\r\nast *\r\nbrakets () {} []\r\ntags <b>bold</b>\r\nquotes 'single' "double"\r\n\r\nline before and after this are empty\r\n
\.


--
-- Name: safeguarding_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('safeguarding_id_seq', 3, true);


--
-- Data for Name: transaction; Type: TABLE DATA; Schema: public; Owner: api
--

COPY transaction (id, report_id, transaction_type_id, amount, more_details) FROM stdin;
10	1	incapacity-benefit	171.20	\N
11	1	income-support	188.32	\N
12	1	pension-credit	205.44	\N
116	2	rent	1985.92	\N
13	1	personal-independence-payment	222.56	\N
14	1	severe-disablement-allowance	239.68	\N
15	1	universal-credit	256.80	\N
16	1	winter-fuel-cold-weather-payment	273.92	\N
17	1	other-benefits	291.04	\N
18	1	personal-pension	308.16	\N
19	1	state-pension	325.28	\N
20	1	compensation-or-damages-award	342.40	\N
21	1	bequest-or-inheritance	359.52	\N
22	1	cash-gift-received	376.64	\N
23	1	refunds	393.76	\N
24	1	sale-of-asset	410.88	\N
25	1	sale-of-investment	428.00	\N
26	1	sale-of-property	445.12	\N
27	1	transfers-in-from-client-s-other-accounts	462.24	\N
28	1	anything-else	479.36	\N
29	1	broadband	496.48	\N
30	1	council-tax	513.60	\N
31	1	electricity	530.72	\N
32	1	food	547.84	\N
33	1	gas	564.96	\N
34	1	insurance-eg-life-home-contents	582.08	\N
35	1	other-insurance	599.20	\N
36	1	property-maintenance-improvement	616.32	\N
37	1	telephone	633.44	\N
38	1	tv-services	650.56	\N
39	1	water	667.68	\N
40	1	households-bills-other	684.80	\N
41	1	accommodation-service-charge	701.92	\N
42	1	mortgage	719.04	\N
43	1	rent	736.16	\N
44	1	accommodation-other	753.28	\N
45	1	care-fees	770.40	\N
46	1	local-authority-charges-for-care	787.52	\N
47	1	medical-expenses	804.64	\N
48	1	medical-insurance	821.76	\N
49	1	client-transport-bus-train-taxi-fares	838.88	\N
50	1	clothes	856.00	\N
51	1	day-trips	873.12	\N
52	1	holidays	890.24	\N
53	1	personal-allowance-pocket-money	907.36	\N
54	1	toiletries	924.48	\N
55	1	deputy-security-bond	941.60	\N
56	1	opg-fees	958.72	\N
57	1	other-fees	975.84	\N
58	1	professional-fees-eg-solicitor-accountant	992.96	\N
59	1	your-deputy-expenses	1010.08	\N
60	1	investment-bonds-purchased	1027.20	\N
61	1	investment-account-purchased	1044.32	\N
62	1	purchase-over-1000	1061.44	\N
63	1	stocks-and-shares-purchased	1078.56	\N
64	1	gifts	1095.68	\N
65	1	bank-charges	1112.80	\N
66	1	credit-cards-charges	1129.92	\N
67	1	unpaid-care-fees	1147.04	\N
68	1	loans	1164.16	\N
69	1	tax-payments-to-hmrc	1181.28	\N
70	1	debt-and-charges-other	1198.40	\N
71	1	cash-withdrawn	1215.52	\N
72	1	transfers-out-to-other-accounts	1232.64	\N
73	1	anything-else-paid-out	1249.76	\N
74	2	account-interest	1266.88	\N
75	2	dividends	1284.00	\N
76	2	income-from-investments	1301.12	\N
77	2	income-from-property-rental	1318.24	\N
78	2	salary-or-wages	1335.36	\N
79	2	attendance-allowance	1352.48	\N
80	2	disability-living-allowance	1369.60	\N
81	2	employment-support-allowance	1386.72	\N
82	2	housing-benefit	1403.84	\N
83	2	incapacity-benefit	1420.96	\N
84	2	income-support	1438.08	\N
85	2	pension-credit	1455.20	\N
86	2	personal-independence-payment	1472.32	\N
87	2	severe-disablement-allowance	1489.44	\N
88	2	universal-credit	1506.56	\N
243	4	sale-of-asset	4160.16	\N
89	2	winter-fuel-cold-weather-payment	1523.68	\N
90	2	other-benefits	1540.80	\N
91	2	personal-pension	1557.92	\N
92	2	state-pension	1575.04	\N
93	2	compensation-or-damages-award	1592.16	\N
94	2	bequest-or-inheritance	1609.28	\N
95	2	cash-gift-received	1626.40	\N
96	2	refunds	1643.52	\N
97	2	sale-of-asset	1660.64	\N
98	2	sale-of-investment	1677.76	\N
99	2	sale-of-property	1694.88	\N
100	2	transfers-in-from-client-s-other-accounts	1712.00	\N
101	2	anything-else	1729.12	\N
102	2	broadband	1746.24	\N
103	2	council-tax	1763.36	\N
104	2	electricity	1780.48	\N
105	2	food	1797.60	\N
106	2	gas	1814.72	\N
107	2	insurance-eg-life-home-contents	1831.84	\N
108	2	other-insurance	1848.96	\N
109	2	property-maintenance-improvement	1866.08	\N
110	2	telephone	1883.20	\N
111	2	tv-services	1900.32	\N
112	2	water	1917.44	\N
113	2	households-bills-other	1934.56	\N
114	2	accommodation-service-charge	1951.68	\N
115	2	mortgage	1968.80	\N
117	2	accommodation-other	2003.04	\N
118	2	care-fees	2020.16	\N
119	2	local-authority-charges-for-care	2037.28	\N
120	2	medical-expenses	2054.40	\N
121	2	medical-insurance	2071.52	\N
122	2	client-transport-bus-train-taxi-fares	2088.64	\N
123	2	clothes	2105.76	\N
124	2	day-trips	2122.88	\N
125	2	holidays	2140.00	\N
126	2	personal-allowance-pocket-money	2157.12	\N
127	2	toiletries	2174.24	\N
128	2	deputy-security-bond	2191.36	\N
129	2	opg-fees	2208.48	\N
130	2	other-fees	2225.60	\N
131	2	professional-fees-eg-solicitor-accountant	2242.72	\N
132	2	your-deputy-expenses	2259.84	\N
133	2	investment-bonds-purchased	2276.96	\N
134	2	investment-account-purchased	2294.08	\N
135	2	purchase-over-1000	2311.20	\N
136	2	stocks-and-shares-purchased	2328.32	\N
137	2	gifts	2345.44	\N
138	2	bank-charges	2362.56	\N
139	2	credit-cards-charges	2379.68	\N
140	2	unpaid-care-fees	2396.80	\N
141	2	loans	2413.92	\N
142	2	tax-payments-to-hmrc	2431.04	\N
143	2	debt-and-charges-other	2448.16	\N
144	2	cash-withdrawn	2465.28	\N
145	2	transfers-out-to-other-accounts	2482.40	\N
146	2	anything-else-paid-out	2499.52	\N
147	3	account-interest	2516.64	\N
148	3	dividends	2533.76	\N
149	3	income-from-investments	2550.88	\N
150	3	income-from-property-rental	2568.00	\N
151	3	salary-or-wages	2585.12	\N
152	3	attendance-allowance	2602.24	\N
153	3	disability-living-allowance	2619.36	\N
154	3	employment-support-allowance	2636.48	\N
155	3	housing-benefit	2653.60	\N
156	3	incapacity-benefit	2670.72	\N
157	3	income-support	2687.84	\N
158	3	pension-credit	2704.96	\N
159	3	personal-independence-payment	2722.08	\N
160	3	severe-disablement-allowance	2739.20	\N
161	3	universal-credit	2756.32	\N
162	3	winter-fuel-cold-weather-payment	2773.44	\N
163	3	other-benefits	2790.56	\N
164	3	personal-pension	2807.68	\N
165	3	state-pension	2824.80	\N
166	3	compensation-or-damages-award	2841.92	\N
167	3	bequest-or-inheritance	2859.04	\N
168	3	cash-gift-received	2876.16	\N
169	3	refunds	2893.28	\N
170	3	sale-of-asset	2910.40	\N
171	3	sale-of-investment	2927.52	\N
172	3	sale-of-property	2944.64	\N
173	3	transfers-in-from-client-s-other-accounts	2961.76	\N
174	3	anything-else	2978.88	\N
175	3	broadband	2996.00	\N
176	3	council-tax	3013.12	\N
177	3	electricity	3030.24	\N
178	3	food	3047.36	\N
179	3	gas	3064.48	\N
180	3	insurance-eg-life-home-contents	3081.60	\N
181	3	other-insurance	3098.72	\N
182	3	property-maintenance-improvement	3115.84	\N
183	3	telephone	3132.96	\N
184	3	tv-services	3150.08	\N
185	3	water	3167.20	\N
186	3	households-bills-other	3184.32	\N
187	3	accommodation-service-charge	3201.44	\N
188	3	mortgage	3218.56	\N
189	3	rent	3235.68	\N
190	3	accommodation-other	3252.80	\N
191	3	care-fees	3269.92	\N
192	3	local-authority-charges-for-care	3287.04	\N
193	3	medical-expenses	3304.16	\N
194	3	medical-insurance	3321.28	\N
195	3	client-transport-bus-train-taxi-fares	3338.40	\N
196	3	clothes	3355.52	\N
197	3	day-trips	3372.64	\N
198	3	holidays	3389.76	\N
199	3	personal-allowance-pocket-money	3406.88	\N
200	3	toiletries	3424.00	\N
201	3	deputy-security-bond	3441.12	\N
202	3	opg-fees	3458.24	\N
203	3	other-fees	3475.36	\N
204	3	professional-fees-eg-solicitor-accountant	3492.48	\N
205	3	your-deputy-expenses	3509.60	\N
206	3	investment-bonds-purchased	3526.72	\N
207	3	investment-account-purchased	3543.84	\N
208	3	purchase-over-1000	3560.96	\N
209	3	stocks-and-shares-purchased	3578.08	\N
210	3	gifts	3595.20	\N
211	3	bank-charges	3612.32	\N
212	3	credit-cards-charges	3629.44	\N
213	3	unpaid-care-fees	3646.56	\N
214	3	loans	3663.68	\N
215	3	tax-payments-to-hmrc	3680.80	\N
216	3	debt-and-charges-other	3697.92	\N
217	3	cash-withdrawn	3715.04	\N
218	3	transfers-out-to-other-accounts	3732.16	\N
219	3	anything-else-paid-out	3749.28	\N
220	4	account-interest	3766.40	\N
221	4	dividends	3783.52	\N
222	4	income-from-investments	3800.64	\N
223	4	income-from-property-rental	3817.76	\N
224	4	salary-or-wages	3834.88	\N
225	4	attendance-allowance	3852.00	\N
226	4	disability-living-allowance	3869.12	\N
227	4	employment-support-allowance	3886.24	\N
228	4	housing-benefit	3903.36	\N
229	4	incapacity-benefit	3920.48	\N
230	4	income-support	3937.60	\N
231	4	pension-credit	3954.72	\N
232	4	personal-independence-payment	3971.84	\N
233	4	severe-disablement-allowance	3988.96	\N
234	4	universal-credit	4006.08	\N
235	4	winter-fuel-cold-weather-payment	4023.20	\N
236	4	other-benefits	4040.32	\N
237	4	personal-pension	4057.44	\N
238	4	state-pension	4074.56	\N
239	4	compensation-or-damages-award	4091.68	\N
240	4	bequest-or-inheritance	4108.80	\N
241	4	cash-gift-received	4125.92	\N
242	4	refunds	4143.04	\N
244	4	sale-of-investment	4177.28	\N
245	4	sale-of-property	4194.40	\N
246	4	transfers-in-from-client-s-other-accounts	4211.52	\N
247	4	anything-else	4228.64	\N
248	4	broadband	4245.76	\N
249	4	council-tax	4262.88	\N
250	4	electricity	4280.00	\N
251	4	food	4297.12	\N
252	4	gas	4314.24	\N
253	4	insurance-eg-life-home-contents	4331.36	\N
254	4	other-insurance	4348.48	\N
255	4	property-maintenance-improvement	4365.60	\N
256	4	telephone	4382.72	\N
257	4	tv-services	4399.84	\N
258	4	water	4416.96	\N
259	4	households-bills-other	4434.08	\N
260	4	accommodation-service-charge	4451.20	\N
261	4	mortgage	4468.32	\N
262	4	rent	4485.44	\N
263	4	accommodation-other	4502.56	\N
264	4	care-fees	4519.68	\N
265	4	local-authority-charges-for-care	4536.80	\N
266	4	medical-expenses	4553.92	\N
267	4	medical-insurance	4571.04	\N
268	4	client-transport-bus-train-taxi-fares	4588.16	\N
269	4	clothes	4605.28	\N
270	4	day-trips	4622.40	\N
271	4	holidays	4639.52	\N
272	4	personal-allowance-pocket-money	4656.64	\N
273	4	toiletries	4673.76	\N
274	4	deputy-security-bond	4690.88	\N
275	4	opg-fees	4708.00	\N
276	4	other-fees	4725.12	\N
277	4	professional-fees-eg-solicitor-accountant	4742.24	\N
278	4	your-deputy-expenses	4759.36	\N
279	4	investment-bonds-purchased	4776.48	\N
280	4	investment-account-purchased	4793.60	\N
281	4	purchase-over-1000	4810.72	\N
282	4	stocks-and-shares-purchased	4827.84	\N
283	4	gifts	4844.96	\N
284	4	bank-charges	4862.08	\N
285	4	credit-cards-charges	4879.20	\N
286	4	unpaid-care-fees	4896.32	\N
287	4	loans	4913.44	\N
288	4	tax-payments-to-hmrc	4930.56	\N
289	4	debt-and-charges-other	4947.68	\N
290	4	cash-withdrawn	4964.80	\N
291	4	transfers-out-to-other-accounts	4981.92	\N
292	4	anything-else-paid-out	4999.04	\N
5	1	salary-or-wages	0.00	\N
6	1	attendance-allowance	0.00	\N
7	1	disability-living-allowance	0.00	\N
8	1	employment-support-allowance	0.00	\N
9	1	housing-benefit	0.00	\N
1	1	account-interest	\N	\N
2	1	dividends	\N	\N
3	1	income-from-investments	\N	\N
4	1	income-from-property-rental	\N	\N
\.


--
-- Name: transaction_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('transaction_id_seq', 292, true);


--
-- Data for Name: transaction_type; Type: TABLE DATA; Schema: public; Owner: api
--

COPY transaction_type (id, has_more_details, display_order, category, type) FROM stdin;
account-interest	f	2	income-and-earnings	in
dividends	f	3	income-and-earnings	in
income-from-investments	f	4	income-and-earnings	in
income-from-property-rental	f	5	income-and-earnings	in
salary-or-wages	f	6	income-and-earnings	in
attendance-allowance	f	7	state-benefits	in
disability-living-allowance	f	8	state-benefits	in
employment-support-allowance	f	9	state-benefits	in
housing-benefit	f	10	state-benefits	in
incapacity-benefit	f	11	state-benefits	in
income-support	f	12	state-benefits	in
pension-credit	f	13	state-benefits	in
personal-independence-payment	f	14	state-benefits	in
severe-disablement-allowance	f	15	state-benefits	in
universal-credit	f	16	state-benefits	in
winter-fuel-cold-weather-payment	f	17	state-benefits	in
other-benefits	t	18	state-benefits	in
personal-pension	f	19	pensions	in
state-pension	f	20	pensions	in
compensation-or-damages-award	t	21	damages	in
bequest-or-inheritance	f	22	one-off	in
cash-gift-received	f	23	one-off	in
refunds	f	24	one-off	in
sale-of-asset	t	25	one-off	in
sale-of-investment	t	26	one-off	in
sale-of-property	t	27	one-off	in
transfers-in-from-client-s-other-accounts	t	28	moving-money	in
anything-else	t	29	moneyin-other	in
broadband	f	30	household-bills	out
council-tax	f	31	household-bills	out
electricity	f	32	household-bills	out
food	f	33	household-bills	out
gas	f	34	household-bills	out
insurance-eg-life-home-contents	f	35	household-bills	out
other-insurance	f	36	household-bills	out
property-maintenance-improvement	t	37	household-bills	out
telephone	f	38	household-bills	out
tv-services	f	39	household-bills	out
water	f	40	household-bills	out
households-bills-other	t	41	household-bills	out
accommodation-service-charge	f	42	accommodation	out
mortgage	f	43	accommodation	out
rent	f	44	accommodation	out
accommodation-other	t	45	accommodation	out
care-fees	f	46	care-and-medical	out
local-authority-charges-for-care	f	47	care-and-medical	out
medical-expenses	f	48	care-and-medical	out
medical-insurance	f	49	care-and-medical	out
client-transport-bus-train-taxi-fares	f	50	client-expenses	out
clothes	f	51	client-expenses	out
day-trips	f	52	client-expenses	out
holidays	f	53	client-expenses	out
personal-allowance-pocket-money	f	54	client-expenses	out
toiletries	f	55	client-expenses	out
deputy-security-bond	f	56	fees	out
opg-fees	f	57	fees	out
other-fees	t	58	fees	out
professional-fees-eg-solicitor-accountant	t	59	fees	out
your-deputy-expenses	t	60	fees	out
investment-bonds-purchased	t	61	major-purchases	out
investment-account-purchased	t	62	major-purchases	out
purchase-over-1000	t	63	major-purchases	out
stocks-and-shares-purchased	t	64	major-purchases	out
gifts	t	65	spending-on-other-people	out
bank-charges	f	66	debt-and-charges	out
credit-cards-charges	f	67	debt-and-charges	out
unpaid-care-fees	f	68	debt-and-charges	out
loans	f	69	debt-and-charges	out
tax-payments-to-hmrc	f	70	debt-and-charges	out
debt-and-charges-other	t	71	debt-and-charges	out
cash-withdrawn	t	72	moving-money	out
transfers-out-to-other-accounts	t	73	moving-money	out
anything-else-paid-out	t	74	moneyout-other	out
\.


--
-- Name: account_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY account
    ADD CONSTRAINT account_pkey PRIMARY KEY (id);


--
-- Name: account_transaction_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY account_transaction
    ADD CONSTRAINT account_transaction_pkey PRIMARY KEY (id);


--
-- Name: account_transaction_type_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY account_transaction_type
    ADD CONSTRAINT account_transaction_type_pkey PRIMARY KEY (id);


--
-- Name: asset_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY asset
    ADD CONSTRAINT asset_pkey PRIMARY KEY (id);


--
-- Name: audit_log_entry_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY audit_log_entry
    ADD CONSTRAINT audit_log_entry_pkey PRIMARY KEY (id);


--
-- Name: casrec_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY casrec
    ADD CONSTRAINT casrec_pkey PRIMARY KEY (id);


--
-- Name: client_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY client
    ADD CONSTRAINT client_pkey PRIMARY KEY (id);


--
-- Name: contact_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT contact_pkey PRIMARY KEY (id);


--
-- Name: court_order_type_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY court_order_type
    ADD CONSTRAINT court_order_type_pkey PRIMARY KEY (id);


--
-- Name: dd_user_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY dd_user
    ADD CONSTRAINT dd_user_pkey PRIMARY KEY (id);


--
-- Name: decision_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY decision
    ADD CONSTRAINT decision_pkey PRIMARY KEY (id);


--
-- Name: deputy_case_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY deputy_case
    ADD CONSTRAINT deputy_case_pkey PRIMARY KEY (client_id, user_id);


--
-- Name: migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (version);


--
-- Name: report_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY report
    ADD CONSTRAINT report_pkey PRIMARY KEY (id);


--
-- Name: role_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_pkey PRIMARY KEY (id);


--
-- Name: safeguarding_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY safeguarding
    ADD CONSTRAINT safeguarding_pkey PRIMARY KEY (id);


--
-- Name: transaction_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY transaction
    ADD CONSTRAINT transaction_pkey PRIMARY KEY (id);


--
-- Name: transaction_type_pkey; Type: CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY transaction_type
    ADD CONSTRAINT transaction_type_pkey PRIMARY KEY (id);


--
-- Name: idx_2af5a5c4bd2a4c0; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_2af5a5c4bd2a4c0 ON asset USING btree (report_id);


--
-- Name: idx_4c62e6384bd2a4c0; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_4c62e6384bd2a4c0 ON contact USING btree (report_id);


--
-- Name: idx_6764ab8bd60322ac; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_6764ab8bd60322ac ON dd_user USING btree (role_id);


--
-- Name: idx_723705d14bd2a4c0; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_723705d14bd2a4c0 ON transaction USING btree (report_id);


--
-- Name: idx_723705d1b3e6b071; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_723705d1b3e6b071 ON transaction USING btree (transaction_type_id);


--
-- Name: idx_7d3656a44bd2a4c0; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_7d3656a44bd2a4c0 ON account USING btree (report_id);


--
-- Name: idx_7f52717019eb6921; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_7f52717019eb6921 ON deputy_case USING btree (client_id);


--
-- Name: idx_7f527170a76ed395; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_7f527170a76ed395 ON deputy_case USING btree (user_id);


--
-- Name: idx_84acbe484bd2a4c0; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_84acbe484bd2a4c0 ON decision USING btree (report_id);


--
-- Name: idx_a370f9d2387f8b02; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_a370f9d2387f8b02 ON account_transaction USING btree (account_transaction_type_id);


--
-- Name: idx_a370f9d29b6b5fba; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_a370f9d29b6b5fba ON account_transaction USING btree (account_id);


--
-- Name: idx_c42f778419eb6921; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_c42f778419eb6921 ON report USING btree (client_id);


--
-- Name: idx_c42f7784a47aeb9; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_c42f7784a47aeb9 ON report USING btree (court_order_type_id);


--
-- Name: idx_d2d938a243f2ed96; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_d2d938a243f2ed96 ON audit_log_entry USING btree (performed_by_user_id);


--
-- Name: idx_d2d938a256b7314a; Type: INDEX; Schema: public; Owner: api
--

CREATE INDEX idx_d2d938a256b7314a ON audit_log_entry USING btree (user_edited_id);


--
-- Name: report_unique_trans; Type: INDEX; Schema: public; Owner: api
--

CREATE UNIQUE INDEX report_unique_trans ON transaction USING btree (report_id, transaction_type_id);


--
-- Name: uniq_6764ab8be7927c74; Type: INDEX; Schema: public; Owner: api
--

CREATE UNIQUE INDEX uniq_6764ab8be7927c74 ON dd_user USING btree (email);


--
-- Name: uniq_8c7877184bd2a4c0; Type: INDEX; Schema: public; Owner: api
--

CREATE UNIQUE INDEX uniq_8c7877184bd2a4c0 ON safeguarding USING btree (report_id);


--
-- Name: unique_trans; Type: INDEX; Schema: public; Owner: api
--

CREATE UNIQUE INDEX unique_trans ON account_transaction USING btree (account_id, account_transaction_type_id);


--
-- Name: fk_2af5a5c4bd2a4c0; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY asset
    ADD CONSTRAINT fk_2af5a5c4bd2a4c0 FOREIGN KEY (report_id) REFERENCES report(id);


--
-- Name: fk_4c62e6384bd2a4c0; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT fk_4c62e6384bd2a4c0 FOREIGN KEY (report_id) REFERENCES report(id);


--
-- Name: fk_6764ab8bd60322ac; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY dd_user
    ADD CONSTRAINT fk_6764ab8bd60322ac FOREIGN KEY (role_id) REFERENCES role(id);


--
-- Name: fk_723705d14bd2a4c0; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY transaction
    ADD CONSTRAINT fk_723705d14bd2a4c0 FOREIGN KEY (report_id) REFERENCES report(id);


--
-- Name: fk_723705d1b3e6b071; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY transaction
    ADD CONSTRAINT fk_723705d1b3e6b071 FOREIGN KEY (transaction_type_id) REFERENCES transaction_type(id);


--
-- Name: fk_7d3656a44bd2a4c0; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY account
    ADD CONSTRAINT fk_7d3656a44bd2a4c0 FOREIGN KEY (report_id) REFERENCES report(id);


--
-- Name: fk_7f52717019eb6921; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY deputy_case
    ADD CONSTRAINT fk_7f52717019eb6921 FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE CASCADE;


--
-- Name: fk_7f527170a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY deputy_case
    ADD CONSTRAINT fk_7f527170a76ed395 FOREIGN KEY (user_id) REFERENCES dd_user(id) ON DELETE CASCADE;


--
-- Name: fk_84acbe484bd2a4c0; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY decision
    ADD CONSTRAINT fk_84acbe484bd2a4c0 FOREIGN KEY (report_id) REFERENCES report(id);


--
-- Name: fk_8c7877184bd2a4c0; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY safeguarding
    ADD CONSTRAINT fk_8c7877184bd2a4c0 FOREIGN KEY (report_id) REFERENCES report(id);


--
-- Name: fk_a370f9d2387f8b02; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY account_transaction
    ADD CONSTRAINT fk_a370f9d2387f8b02 FOREIGN KEY (account_transaction_type_id) REFERENCES account_transaction_type(id);


--
-- Name: fk_a370f9d29b6b5fba; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY account_transaction
    ADD CONSTRAINT fk_a370f9d29b6b5fba FOREIGN KEY (account_id) REFERENCES account(id);


--
-- Name: fk_c42f778419eb6921; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY report
    ADD CONSTRAINT fk_c42f778419eb6921 FOREIGN KEY (client_id) REFERENCES client(id);


--
-- Name: fk_c42f7784a47aeb9; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY report
    ADD CONSTRAINT fk_c42f7784a47aeb9 FOREIGN KEY (court_order_type_id) REFERENCES court_order_type(id);


--
-- Name: fk_d2d938a243f2ed96; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY audit_log_entry
    ADD CONSTRAINT fk_d2d938a243f2ed96 FOREIGN KEY (performed_by_user_id) REFERENCES dd_user(id) ON DELETE SET NULL;


--
-- Name: fk_d2d938a256b7314a; Type: FK CONSTRAINT; Schema: public; Owner: api
--

ALTER TABLE ONLY audit_log_entry
    ADD CONSTRAINT fk_d2d938a256b7314a FOREIGN KEY (user_edited_id) REFERENCES dd_user(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

