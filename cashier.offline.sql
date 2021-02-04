--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: sales_tender; Type: TABLE; Schema: public; Owner: Jared; Tablespace: 
--

CREATE TABLE offline.sales_tender (
    sales_tender_id bigserial NOT NULL,
    sales_header_id bigint,
    tender_id bigint,
    account_id bigint,
    account character varying(30),
    cardno character varying(25),
    amount numeric(10,2),
    carddate character varying(10),
    service_charge numeric(10,2),
    remark text
);


--
-- Name: sales_tender_pkey; Type: CONSTRAINT; Schema: public; Owner: Jared; Tablespace: 
--

ALTER TABLE ONLY offline.sales_tender
    ADD CONSTRAINT sales_tender_pkey PRIMARY KEY (sales_tender_id);


--
-- Name: sales_tender_sales_header_id; Type: INDEX; Schema: public; Owner: Jared; Tablespace: 
--

CREATE INDEX sales_tender_sales_header_id ON sales_tender USING btree (sales_header_id);


--
-- PostgreSQL database dump complete
--


--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: sales_tender; Type: TABLE; Schema: public; Owner: Jared; Tablespace: 
--

CREATE TABLE offline.sales_tender (
    sales_tender_id bigserial NOT NULL,
    sales_header_id bigint,
    tender_id bigint,
    account_id bigint,
    account character varying(30),
    cardno character varying(25),
    amount numeric(10,2),
    carddate character varying(10),
    service_charge numeric(10,2),
    remark text
);


--
-- Name: sales_tender_pkey; Type: CONSTRAINT; Schema: public; Owner: Jared; Tablespace: 
--

ALTER TABLE ONLY offline.sales_tender
    ADD CONSTRAINT sales_tender_pkey PRIMARY KEY (sales_tender_id);


--
-- Name: sales_tender_sales_header_id; Type: INDEX; Schema: public; Owner: Jared; Tablespace: 
--

CREATE INDEX sales_tender_sales_header_id ON sales_tender USING btree (sales_header_id);


--
-- PostgreSQL database dump complete
--

