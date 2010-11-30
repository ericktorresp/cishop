--
-- PostgreSQL database dump
--

-- Dumped from database version 9.0.1
-- Dumped by pg_dump version 9.0.1
-- Started on 2010-11-30 17:02:52

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 313 (class 2612 OID 11574)
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: postgres
--

CREATE OR REPLACE PROCEDURAL LANGUAGE plpgsql;


ALTER PROCEDURAL LANGUAGE plpgsql OWNER TO postgres;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1505 (class 1259 OID 16600)
-- Dependencies: 5
-- Name: game_category; Type: TABLE; Schema: public; Owner: root; Tablespace: 
--

CREATE TABLE game_category (
    id integer NOT NULL,
    ctime timestamp without time zone,
    name character varying(99) NOT NULL
);


ALTER TABLE public.game_category OWNER TO root;

--
-- TOC entry 1506 (class 1259 OID 16605)
-- Dependencies: 5
-- Name: game_games; Type: TABLE; Schema: public; Owner: root; Tablespace: 
--

CREATE TABLE game_games (
    id integer NOT NULL,
    name character varying(99) NOT NULL,
    category_id integer
);


ALTER TABLE public.game_games OWNER TO root;

--
-- TOC entry 1507 (class 1259 OID 16610)
-- Dependencies: 5
-- Name: game_rooms; Type: TABLE; Schema: public; Owner: root; Tablespace: 
--

CREATE TABLE game_rooms (
    id integer NOT NULL,
    name character varying(99),
    game_id integer NOT NULL
);


ALTER TABLE public.game_rooms OWNER TO root;

--
-- TOC entry 1508 (class 1259 OID 16615)
-- Dependencies: 5
-- Name: users; Type: TABLE; Schema: public; Owner: root; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    ctime timestamp without time zone NOT NULL,
    email character varying(99) NOT NULL,
    password character varying(39) NOT NULL,
    status smallint NOT NULL,
    username character varying(99) NOT NULL
);


ALTER TABLE public.users OWNER TO root;

--
-- TOC entry 1796 (class 0 OID 16600)
-- Dependencies: 1505
-- Data for Name: game_category; Type: TABLE DATA; Schema: public; Owner: root
--

COPY game_category (id, ctime, name) FROM stdin;
1	2010-11-30 12:00:00	poker
\.


--
-- TOC entry 1797 (class 0 OID 16605)
-- Dependencies: 1506
-- Data for Name: game_games; Type: TABLE DATA; Schema: public; Owner: root
--

COPY game_games (id, name, category_id) FROM stdin;
1	sdfdsf	1
\.


--
-- TOC entry 1798 (class 0 OID 16610)
-- Dependencies: 1507
-- Data for Name: game_rooms; Type: TABLE DATA; Schema: public; Owner: root
--

COPY game_rooms (id, name, game_id) FROM stdin;
1	room_1_1	1
\.


--
-- TOC entry 1799 (class 0 OID 16615)
-- Dependencies: 1508
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: root
--

COPY users (id, ctime, email, password, status, username) FROM stdin;
\.


--
-- TOC entry 1787 (class 2606 OID 16604)
-- Dependencies: 1505 1505
-- Name: game_category_pkey; Type: CONSTRAINT; Schema: public; Owner: root; Tablespace: 
--

ALTER TABLE ONLY game_category
    ADD CONSTRAINT game_category_pkey PRIMARY KEY (id);


--
-- TOC entry 1789 (class 2606 OID 16609)
-- Dependencies: 1506 1506
-- Name: game_games_pkey; Type: CONSTRAINT; Schema: public; Owner: root; Tablespace: 
--

ALTER TABLE ONLY game_games
    ADD CONSTRAINT game_games_pkey PRIMARY KEY (id);


--
-- TOC entry 1791 (class 2606 OID 16614)
-- Dependencies: 1507 1507
-- Name: game_rooms_pkey; Type: CONSTRAINT; Schema: public; Owner: root; Tablespace: 
--

ALTER TABLE ONLY game_rooms
    ADD CONSTRAINT game_rooms_pkey PRIMARY KEY (id);


--
-- TOC entry 1793 (class 2606 OID 16619)
-- Dependencies: 1508 1508
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: root; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 1794 (class 2606 OID 16620)
-- Dependencies: 1505 1786 1506
-- Name: fk3925a43416f1994e; Type: FK CONSTRAINT; Schema: public; Owner: root
--

ALTER TABLE ONLY game_games
    ADD CONSTRAINT fk3925a43416f1994e FOREIGN KEY (category_id) REFERENCES game_category(id);


--
-- TOC entry 1795 (class 2606 OID 16625)
-- Dependencies: 1506 1507 1788
-- Name: fk39c70c6b8aac4b75; Type: FK CONSTRAINT; Schema: public; Owner: root
--

ALTER TABLE ONLY game_rooms
    ADD CONSTRAINT fk39c70c6b8aac4b75 FOREIGN KEY (game_id) REFERENCES game_games(id);


--
-- TOC entry 1804 (class 0 OID 0)
-- Dependencies: 5
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2010-11-30 17:02:53

--
-- PostgreSQL database dump complete
--

