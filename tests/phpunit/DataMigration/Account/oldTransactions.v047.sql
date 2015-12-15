--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

ALTER TABLE ONLY public.audit_log_entry DROP CONSTRAINT fk_d2d938a256b7314a;
ALTER TABLE ONLY public.audit_log_entry DROP CONSTRAINT fk_d2d938a243f2ed96;
ALTER TABLE ONLY public.report DROP CONSTRAINT fk_c42f7784a47aeb9;
ALTER TABLE ONLY public.report DROP CONSTRAINT fk_c42f778419eb6921;
ALTER TABLE ONLY public.account_transaction DROP CONSTRAINT fk_a370f9d29b6b5fba;
ALTER TABLE ONLY public.account_transaction DROP CONSTRAINT fk_a370f9d2387f8b02;
ALTER TABLE ONLY public.safeguarding DROP CONSTRAINT fk_8c7877184bd2a4c0;
ALTER TABLE ONLY public.decision DROP CONSTRAINT fk_84acbe484bd2a4c0;
ALTER TABLE ONLY public.deputy_case DROP CONSTRAINT fk_7f527170a76ed395;
ALTER TABLE ONLY public.deputy_case DROP CONSTRAINT fk_7f52717019eb6921;
ALTER TABLE ONLY public.account DROP CONSTRAINT fk_7d3656a44bd2a4c0;
ALTER TABLE ONLY public.dd_user DROP CONSTRAINT fk_6764ab8bd60322ac;
ALTER TABLE ONLY public.contact DROP CONSTRAINT fk_4c62e6384bd2a4c0;
ALTER TABLE ONLY public.asset DROP CONSTRAINT fk_2af5a5c4bd2a4c0;
DROP INDEX public.unique_trans;
DROP INDEX public.uniq_8c7877184bd2a4c0;
DROP INDEX public.uniq_6764ab8be7927c74;
DROP INDEX public.idx_d2d938a256b7314a;
DROP INDEX public.idx_d2d938a243f2ed96;
DROP INDEX public.idx_c42f7784a47aeb9;
DROP INDEX public.idx_c42f778419eb6921;
DROP INDEX public.idx_a370f9d29b6b5fba;
DROP INDEX public.idx_a370f9d2387f8b02;
DROP INDEX public.idx_84acbe484bd2a4c0;
DROP INDEX public.idx_7f527170a76ed395;
DROP INDEX public.idx_7f52717019eb6921;
DROP INDEX public.idx_7d3656a44bd2a4c0;
DROP INDEX public.idx_6764ab8bd60322ac;
DROP INDEX public.idx_4c62e6384bd2a4c0;
DROP INDEX public.idx_2af5a5c4bd2a4c0;
ALTER TABLE ONLY public.safeguarding DROP CONSTRAINT safeguarding_pkey;
ALTER TABLE ONLY public.role DROP CONSTRAINT role_pkey;
ALTER TABLE ONLY public.report DROP CONSTRAINT report_pkey;
ALTER TABLE ONLY public.migrations DROP CONSTRAINT migrations_pkey;
ALTER TABLE ONLY public.deputy_case DROP CONSTRAINT deputy_case_pkey;
ALTER TABLE ONLY public.decision DROP CONSTRAINT decision_pkey;
ALTER TABLE ONLY public.dd_user DROP CONSTRAINT dd_user_pkey;
ALTER TABLE ONLY public.court_order_type DROP CONSTRAINT court_order_type_pkey;
ALTER TABLE ONLY public.contact DROP CONSTRAINT contact_pkey;
ALTER TABLE ONLY public.client DROP CONSTRAINT client_pkey;
ALTER TABLE ONLY public.casrec DROP CONSTRAINT casrec_pkey;
ALTER TABLE ONLY public.audit_log_entry DROP CONSTRAINT audit_log_entry_pkey;
ALTER TABLE ONLY public.asset DROP CONSTRAINT asset_pkey;
ALTER TABLE ONLY public.account_transaction_type DROP CONSTRAINT account_transaction_type_pkey;
ALTER TABLE ONLY public.account_transaction DROP CONSTRAINT account_transaction_pkey;
ALTER TABLE ONLY public.account DROP CONSTRAINT account_pkey;
ALTER TABLE public.safeguarding ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.role ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.report ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.decision ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.dd_user ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.court_order_type ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.contact ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.client ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.casrec ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.audit_log_entry ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.asset ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.account_transaction ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.account ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.safeguarding_id_seq;
DROP TABLE public.safeguarding;
DROP SEQUENCE public.role_id_seq;
DROP TABLE public.role;
DROP SEQUENCE public.report_id_seq;
DROP TABLE public.report;
DROP TABLE public.migrations;
DROP TABLE public.deputy_case;
DROP SEQUENCE public.decision_id_seq;
DROP TABLE public.decision;
DROP SEQUENCE public.dd_user_id_seq;
DROP TABLE public.dd_user;
DROP SEQUENCE public.court_order_type_id_seq;
DROP TABLE public.court_order_type;
DROP SEQUENCE public.contact_id_seq;
DROP TABLE public.contact;
DROP SEQUENCE public.client_id_seq;
DROP TABLE public.client;
DROP SEQUENCE public.casrec_id_seq;
DROP TABLE public.casrec;
DROP SEQUENCE public.audit_log_entry_id_seq;
DROP TABLE public.audit_log_entry;
DROP SEQUENCE public.asset_id_seq;
DROP TABLE public.asset;
DROP TABLE public.account_transaction_type;
DROP SEQUENCE public.account_transaction_id_seq;
DROP TABLE public.account_transaction;
DROP SEQUENCE public.account_id_seq;
DROP TABLE public.account;
DROP EXTENSION plpgsql;
DROP SCHEMA public;
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
-- Name: account; Type: TABLE; Schema: public; Owner: api; Tablespace: 
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
    closing_date_explanation text
);


ALTER TABLE public.account OWNER TO api;

--
-- Name: account_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE account_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.account_id_seq OWNER TO api;

--
-- Name: account_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE account_id_seq OWNED BY account.id;


--
-- Name: account_transaction; Type: TABLE; Schema: public; Owner: api; Tablespace: 
--

CREATE TABLE account_transaction (
    id integer NOT NULL,
    account_id integer,
    account_transaction_type_id character varying(255) DEFAULT NULL::character varying,
    amount numeric(14,2) DEFAULT NULL::numeric,
    more_details text
);


ALTER TABLE public.account_transaction OWNER TO api;

--
-- Name: account_transaction_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE account_transaction_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.account_transaction_id_seq OWNER TO api;

--
-- Name: account_transaction_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE account_transaction_id_seq OWNED BY account_transaction.id;


--
-- Name: account_transaction_type; Type: TABLE; Schema: public; Owner: api; Tablespace: 
--

CREATE TABLE account_transaction_type (
    id character varying(255) NOT NULL,
    has_more_details boolean NOT NULL,
    display_order integer,
    type character varying(255) NOT NULL
);


ALTER TABLE public.account_transaction_type OWNER TO api;

--
-- Name: asset; Type: TABLE; Schema: public; Owner: api; Tablespace: 
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


ALTER TABLE public.asset OWNER TO api;

--
-- Name: asset_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE asset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.asset_id_seq OWNER TO api;

--
-- Name: asset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE asset_id_seq OWNED BY asset.id;


--
-- Name: audit_log_entry; Type: TABLE; Schema: public; Owner: api; Tablespace: 
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


ALTER TABLE public.audit_log_entry OWNER TO api;

--
-- Name: audit_log_entry_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE audit_log_entry_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.audit_log_entry_id_seq OWNER TO api;

--
-- Name: audit_log_entry_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE audit_log_entry_id_seq OWNED BY audit_log_entry.id;


--
-- Name: casrec; Type: TABLE; Schema: public; Owner: api; Tablespace: 
--

CREATE TABLE casrec (
    id integer NOT NULL,
    client_case_number character varying(20) NOT NULL,
    client_lastname character varying(50) NOT NULL,
    deputy_no character varying(100) NOT NULL,
    deputy_lastname character varying(100) DEFAULT NULL::character varying,
    deputy_postcode character varying(10) DEFAULT NULL::character varying
);


ALTER TABLE public.casrec OWNER TO api;

--
-- Name: casrec_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE casrec_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.casrec_id_seq OWNER TO api;

--
-- Name: casrec_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE casrec_id_seq OWNED BY casrec.id;


--
-- Name: client; Type: TABLE; Schema: public; Owner: api; Tablespace: 
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


ALTER TABLE public.client OWNER TO api;

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


ALTER TABLE public.client_id_seq OWNER TO api;

--
-- Name: client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE client_id_seq OWNED BY client.id;


--
-- Name: contact; Type: TABLE; Schema: public; Owner: api; Tablespace: 
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


ALTER TABLE public.contact OWNER TO api;

--
-- Name: contact_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE contact_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.contact_id_seq OWNER TO api;

--
-- Name: contact_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE contact_id_seq OWNED BY contact.id;


--
-- Name: court_order_type; Type: TABLE; Schema: public; Owner: api; Tablespace: 
--

CREATE TABLE court_order_type (
    id integer NOT NULL,
    name character varying(100) DEFAULT NULL::character varying
);


ALTER TABLE public.court_order_type OWNER TO api;

--
-- Name: court_order_type_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE court_order_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.court_order_type_id_seq OWNER TO api;

--
-- Name: court_order_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE court_order_type_id_seq OWNED BY court_order_type.id;


--
-- Name: dd_user; Type: TABLE; Schema: public; Owner: api; Tablespace: 
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


ALTER TABLE public.dd_user OWNER TO api;

--
-- Name: dd_user_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE dd_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dd_user_id_seq OWNER TO api;

--
-- Name: dd_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE dd_user_id_seq OWNED BY dd_user.id;


--
-- Name: decision; Type: TABLE; Schema: public; Owner: api; Tablespace: 
--

CREATE TABLE decision (
    id integer NOT NULL,
    report_id integer,
    description text NOT NULL,
    client_involved_boolean boolean NOT NULL,
    client_involved_details text
);


ALTER TABLE public.decision OWNER TO api;

--
-- Name: decision_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE decision_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.decision_id_seq OWNER TO api;

--
-- Name: decision_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE decision_id_seq OWNED BY decision.id;


--
-- Name: deputy_case; Type: TABLE; Schema: public; Owner: api; Tablespace: 
--

CREATE TABLE deputy_case (
    client_id integer NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.deputy_case OWNER TO api;

--
-- Name: migrations; Type: TABLE; Schema: public; Owner: api; Tablespace: 
--

CREATE TABLE migrations (
    version character varying(255) NOT NULL
);


ALTER TABLE public.migrations OWNER TO api;

--
-- Name: report; Type: TABLE; Schema: public; Owner: api; Tablespace: 
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
    reason_not_all_agreed text
);


ALTER TABLE public.report OWNER TO api;

--
-- Name: report_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE report_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.report_id_seq OWNER TO api;

--
-- Name: report_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE report_id_seq OWNED BY report.id;


--
-- Name: role; Type: TABLE; Schema: public; Owner: api; Tablespace: 
--

CREATE TABLE role (
    id integer NOT NULL,
    name character varying(60) NOT NULL,
    role character varying(50) DEFAULT NULL::character varying
);


ALTER TABLE public.role OWNER TO api;

--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.role_id_seq OWNER TO api;

--
-- Name: role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE role_id_seq OWNED BY role.id;


--
-- Name: safeguarding; Type: TABLE; Schema: public; Owner: api; Tablespace: 
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
    when_was_care_plan_last_reviewed date
);


ALTER TABLE public.safeguarding OWNER TO api;

--
-- Name: safeguarding_id_seq; Type: SEQUENCE; Schema: public; Owner: api
--

CREATE SEQUENCE safeguarding_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.safeguarding_id_seq OWNER TO api;

--
-- Name: safeguarding_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: api
--

ALTER SEQUENCE safeguarding_id_seq OWNED BY safeguarding.id;


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
-- Data for Name: account; Type: TABLE DATA; Schema: public; Owner: api
--

COPY account (id, report_id, bank_name, sort_code, account_number, last_edit, created_at, opening_balance, opening_date_explanation, closing_balance, closing_balance_explanation, opening_date, closing_date, closing_date_explanation) FROM stdin;
1	1	hsbc	121212	1234	2015-12-03 16:30:50	2015-12-03 16:28:44	123.00	\N	123.00	balances does not match	2015-01-01	2015-12-31	closing dates do not match 123
2	2	e1	888888	1234	2015-12-03 16:37:56	2015-12-03 16:37:42	8.00	\N	\N	\N	2015-01-01	\N	\N
3	2	e2	111111	1235	2015-12-03 16:38:30	2015-12-03 16:38:19	9.00	\N	\N	\N	2015-01-01	\N	\N
\.


--
-- Name: account_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('account_id_seq', 3, true);


--
-- Data for Name: account_transaction; Type: TABLE DATA; Schema: public; Owner: api
--

COPY account_transaction (id, account_id, account_transaction_type_id, amount, more_details) FROM stdin;
41	2	disability_living_allowance_or_personal_independence_payment	101.00	\N
42	2	care_fees_or_local_authority_charges_for_care	102.00	\N
1	1	disability_living_allowance_or_personal_independence_payment	1.00	\N
4	1	attendance_allowance	2.00	\N
6	1	employment_support_allowance_or_incapacity_benefit	3.00	\N
7	1	severe_disablement_allowance	4.00	\N
9	1	income_support_or_pension_credit	5.00	\N
12	1	housing_benefit	6.00	\N
13	1	state_pension	7.00	\N
15	1	universal_credit	8.00	\N
18	1	other_benefits_eg_winter_fuel_or_cold_weather_payments	9.00	\N
19	1	occupational_pensions	10.00	\N
21	1	account_interest	11.00	\N
23	1	income_from_investments_property_or_dividends	12.00	\N
25	1	salary_or_wages	13.00	\N
28	1	refunds	14.00	\N
30	1	bequests_eg_inheritance_gifts_received	15.00	\N
31	1	sale_of_investments_property_or_assets	16.00	16,desc
34	1	compensation_or_damages_awards	17.00	17.desc
35	1	transfers_in_from_client_s_other_accounts	18.00	18.desc
37	1	any_other_money_paid_in_and_not_listed_above	19.00	19.desc
2	1	care_fees_or_local_authority_charges_for_care	20.00	\N
3	1	accommodation_costs_eg_rent_mortgage_service_charges	21.00	\N
5	1	household_bills_eg_water_gas_electricity_phone_council_tax	22.00	\N
8	1	day_to_day_living_costs_eg_food_toiletries_clothing_sundries	23.00	\N
10	1	debt_payments_eg_loans_cards_care_fee_arrears	24.00	\N
11	1	travel_costs_for_client_eg_bus_train_taxi_fares	25.00	\N
14	1	holidays_or_day_trips	26.00	\N
16	1	tax_payable_to_hmrc	27.00	\N
17	1	insurance_eg_life_home_and_contents	28.00	\N
20	1	office_of_the_public_guardian_fees	29.00	\N
22	1	deputy_s_security_bond	30.00	\N
24	1	client_s_personal_allowance_eg_spending_money	31.00	31.desc
26	1	cash_withdrawals	32.00	32.desc
27	1	professional_fees_eg_solicitor_or_accountant_fees	33.00	33.desc
29	1	deputy_s_expenses	34.00	34.desc
32	1	gifts	35.00	35.desc
33	1	major_purchases_eg_property_vehicles	36.00	36.desc
36	1	property_maintenance_or_improvement	37.00	37.desc
38	1	investments_eg_shares_bonds_savings	38.00	38.desc
39	1	transfers_out_to_other_client_s_accounts	39.00	39.desc
40	1	any_other_money_paid_out_and_not_listed_above	40.00	40.desc
43	2	accommodation_costs_eg_rent_mortgage_service_charges	\N	\N
44	2	attendance_allowance	\N	\N
45	2	household_bills_eg_water_gas_electricity_phone_council_tax	\N	\N
46	2	employment_support_allowance_or_incapacity_benefit	\N	\N
47	2	severe_disablement_allowance	\N	\N
48	2	day_to_day_living_costs_eg_food_toiletries_clothing_sundries	\N	\N
49	2	income_support_or_pension_credit	\N	\N
50	2	debt_payments_eg_loans_cards_care_fee_arrears	\N	\N
51	2	travel_costs_for_client_eg_bus_train_taxi_fares	\N	\N
52	2	housing_benefit	\N	\N
53	2	state_pension	\N	\N
54	2	holidays_or_day_trips	\N	\N
55	2	universal_credit	\N	\N
56	2	tax_payable_to_hmrc	\N	\N
57	2	insurance_eg_life_home_and_contents	\N	\N
58	2	other_benefits_eg_winter_fuel_or_cold_weather_payments	\N	\N
59	2	occupational_pensions	\N	\N
60	2	office_of_the_public_guardian_fees	\N	\N
61	2	account_interest	\N	\N
62	2	deputy_s_security_bond	\N	\N
63	2	income_from_investments_property_or_dividends	\N	\N
64	2	client_s_personal_allowance_eg_spending_money	\N	\N
65	2	salary_or_wages	\N	\N
66	2	cash_withdrawals	\N	\N
67	2	professional_fees_eg_solicitor_or_accountant_fees	\N	\N
68	2	refunds	\N	\N
69	2	deputy_s_expenses	\N	\N
70	2	bequests_eg_inheritance_gifts_received	\N	\N
71	2	sale_of_investments_property_or_assets	\N	\N
72	2	gifts	\N	\N
73	2	major_purchases_eg_property_vehicles	\N	\N
74	2	compensation_or_damages_awards	1.20	cda_desc1
75	2	transfers_in_from_client_s_other_accounts	\N	\N
76	2	property_maintenance_or_improvement	\N	\N
77	2	any_other_money_paid_in_and_not_listed_above	\N	\N
78	2	investments_eg_shares_bonds_savings	\N	\N
79	2	transfers_out_to_other_client_s_accounts	\N	\N
80	2	any_other_money_paid_out_and_not_listed_above	\N	\N
83	3	accommodation_costs_eg_rent_mortgage_service_charges	\N	\N
84	3	attendance_allowance	\N	\N
85	3	household_bills_eg_water_gas_electricity_phone_council_tax	\N	\N
86	3	employment_support_allowance_or_incapacity_benefit	\N	\N
87	3	severe_disablement_allowance	\N	\N
88	3	day_to_day_living_costs_eg_food_toiletries_clothing_sundries	\N	\N
89	3	income_support_or_pension_credit	\N	\N
90	3	debt_payments_eg_loans_cards_care_fee_arrears	\N	\N
91	3	travel_costs_for_client_eg_bus_train_taxi_fares	\N	\N
92	3	housing_benefit	\N	\N
93	3	state_pension	\N	\N
94	3	holidays_or_day_trips	\N	\N
95	3	universal_credit	\N	\N
96	3	tax_payable_to_hmrc	\N	\N
97	3	insurance_eg_life_home_and_contents	\N	\N
98	3	other_benefits_eg_winter_fuel_or_cold_weather_payments	\N	\N
99	3	occupational_pensions	\N	\N
100	3	office_of_the_public_guardian_fees	\N	\N
101	3	account_interest	\N	\N
102	3	deputy_s_security_bond	\N	\N
103	3	income_from_investments_property_or_dividends	\N	\N
104	3	client_s_personal_allowance_eg_spending_money	\N	\N
105	3	salary_or_wages	\N	\N
106	3	cash_withdrawals	\N	\N
107	3	professional_fees_eg_solicitor_or_accountant_fees	\N	\N
108	3	refunds	\N	\N
109	3	deputy_s_expenses	\N	\N
110	3	bequests_eg_inheritance_gifts_received	\N	\N
111	3	sale_of_investments_property_or_assets	\N	\N
112	3	gifts	\N	\N
113	3	major_purchases_eg_property_vehicles	\N	\N
114	3	compensation_or_damages_awards	3.40	cda_desc2
115	3	transfers_in_from_client_s_other_accounts	\N	\N
116	3	property_maintenance_or_improvement	\N	\N
117	3	any_other_money_paid_in_and_not_listed_above	\N	\N
118	3	investments_eg_shares_bonds_savings	\N	\N
119	3	transfers_out_to_other_client_s_accounts	\N	\N
120	3	any_other_money_paid_out_and_not_listed_above	\N	\N
81	3	disability_living_allowance_or_personal_independence_payment	91.00	\N
82	3	care_fees_or_local_authority_charges_for_care	92.00	\N
\.


--
-- Name: account_transaction_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('account_transaction_id_seq', 120, true);


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
-- Name: audit_log_entry_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('audit_log_entry_id_seq', 2, true);


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
1	12345678	\N	12345675432113456	petty france	\N	\N	sw1	AM	john	white	a:2:{i:0;i:2;i:1;i:1;}	2015-01-01	\N
2	12345678	\N	12345675432113456	petty france	\N	\N	sw1	AM	john	white	a:2:{i:0;i:2;i:1;i:1;}	2015-01-01	\N
\.


--
-- Name: client_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('client_id_seq', 2, true);


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
1	5	AD user	AD surname	test	test1@publicguardian.gsi.gov.uk	t	\N	2015-12-03 14:22:49		\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
3	2	Lay Deputy	User	test	test2@publicguardian.gsi.gov.uk	t	\N	2015-12-03 14:22:49		\N	\N	plat house	lyon road	\N	ha12ex	GB	123456789754	\N	2015-12-03 16:28:26	\N
4	2	eee	ccc	test	test3@publicguardian.gsi.gov.uk	t	\N	2015-12-03 16:36:38		\N	2015-12-03 16:36:38	plat house	lyon road	\N	ha12ex	GB	123456789754	\N	2015-12-03 16:37:12	\N
\.


--
-- Name: dd_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('dd_user_id_seq', 4, true);


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
\.


--
-- Data for Name: report; Type: TABLE DATA; Schema: public; Owner: api
--

COPY report (id, client_id, court_order_type_id, title, start_date, end_date, submit_date, last_edit, further_information, no_asset_to_add, reason_for_no_contacts, reason_for_no_decisions, submitted, reviewed, report_seen, all_agreed, reason_not_all_agreed) FROM stdin;
1	1	2	\N	2015-01-01	2015-05-01	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N
2	2	2	\N	2015-01-01	2015-12-31	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N
\.


--
-- Name: report_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('report_id_seq', 2, true);


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

COPY safeguarding (id, report_id, do_you_live_with_client, how_often_do_you_visit, how_often_do_you_phone_or_video_call, how_often_do_you_write_email_or_letter, how_often_does_client_see_other_people, anything_else_to_tell, does_client_receive_paid_care, how_is_care_funded, who_is_doing_the_caring, does_client_have_a_care_plan, when_was_care_plan_last_reviewed) FROM stdin;
\.


--
-- Name: safeguarding_id_seq; Type: SEQUENCE SET; Schema: public; Owner: api
--

SELECT pg_catalog.setval('safeguarding_id_seq', 1, false);


--
-- Name: account_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY account
    ADD CONSTRAINT account_pkey PRIMARY KEY (id);


--
-- Name: account_transaction_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY account_transaction
    ADD CONSTRAINT account_transaction_pkey PRIMARY KEY (id);


--
-- Name: account_transaction_type_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY account_transaction_type
    ADD CONSTRAINT account_transaction_type_pkey PRIMARY KEY (id);


--
-- Name: asset_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY asset
    ADD CONSTRAINT asset_pkey PRIMARY KEY (id);


--
-- Name: audit_log_entry_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY audit_log_entry
    ADD CONSTRAINT audit_log_entry_pkey PRIMARY KEY (id);


--
-- Name: casrec_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY casrec
    ADD CONSTRAINT casrec_pkey PRIMARY KEY (id);


--
-- Name: client_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY client
    ADD CONSTRAINT client_pkey PRIMARY KEY (id);


--
-- Name: contact_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT contact_pkey PRIMARY KEY (id);


--
-- Name: court_order_type_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY court_order_type
    ADD CONSTRAINT court_order_type_pkey PRIMARY KEY (id);


--
-- Name: dd_user_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY dd_user
    ADD CONSTRAINT dd_user_pkey PRIMARY KEY (id);


--
-- Name: decision_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY decision
    ADD CONSTRAINT decision_pkey PRIMARY KEY (id);


--
-- Name: deputy_case_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY deputy_case
    ADD CONSTRAINT deputy_case_pkey PRIMARY KEY (client_id, user_id);


--
-- Name: migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (version);


--
-- Name: report_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY report
    ADD CONSTRAINT report_pkey PRIMARY KEY (id);


--
-- Name: role_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_pkey PRIMARY KEY (id);


--
-- Name: safeguarding_pkey; Type: CONSTRAINT; Schema: public; Owner: api; Tablespace: 
--

ALTER TABLE ONLY safeguarding
    ADD CONSTRAINT safeguarding_pkey PRIMARY KEY (id);


--
-- Name: idx_2af5a5c4bd2a4c0; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_2af5a5c4bd2a4c0 ON asset USING btree (report_id);


--
-- Name: idx_4c62e6384bd2a4c0; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_4c62e6384bd2a4c0 ON contact USING btree (report_id);


--
-- Name: idx_6764ab8bd60322ac; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_6764ab8bd60322ac ON dd_user USING btree (role_id);


--
-- Name: idx_7d3656a44bd2a4c0; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_7d3656a44bd2a4c0 ON account USING btree (report_id);


--
-- Name: idx_7f52717019eb6921; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_7f52717019eb6921 ON deputy_case USING btree (client_id);


--
-- Name: idx_7f527170a76ed395; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_7f527170a76ed395 ON deputy_case USING btree (user_id);


--
-- Name: idx_84acbe484bd2a4c0; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_84acbe484bd2a4c0 ON decision USING btree (report_id);


--
-- Name: idx_a370f9d2387f8b02; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_a370f9d2387f8b02 ON account_transaction USING btree (account_transaction_type_id);


--
-- Name: idx_a370f9d29b6b5fba; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_a370f9d29b6b5fba ON account_transaction USING btree (account_id);


--
-- Name: idx_c42f778419eb6921; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_c42f778419eb6921 ON report USING btree (client_id);


--
-- Name: idx_c42f7784a47aeb9; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_c42f7784a47aeb9 ON report USING btree (court_order_type_id);


--
-- Name: idx_d2d938a243f2ed96; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_d2d938a243f2ed96 ON audit_log_entry USING btree (performed_by_user_id);


--
-- Name: idx_d2d938a256b7314a; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE INDEX idx_d2d938a256b7314a ON audit_log_entry USING btree (user_edited_id);


--
-- Name: uniq_6764ab8be7927c74; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE UNIQUE INDEX uniq_6764ab8be7927c74 ON dd_user USING btree (email);


--
-- Name: uniq_8c7877184bd2a4c0; Type: INDEX; Schema: public; Owner: api; Tablespace: 
--

CREATE UNIQUE INDEX uniq_8c7877184bd2a4c0 ON safeguarding USING btree (report_id);


--
-- Name: unique_trans; Type: INDEX; Schema: public; Owner: api; Tablespace: 
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

