/**
 * "Still compiling" — how Fernando evolved as a developer.
 *
 * EDIT THIS FILE to tell the story. Each stage covers a range of years and is
 * shown while the year counter passes through it.
 *
 * Every stage below is written ONLY from verified information (the CV and the
 * current website). They describe what the work involved, not what was
 * learned. Fill in `learned` and `tech` with your own words, then set
 * `confirmed: true`. Fields left empty are simply not shown.
 */

export type Lang = "en" | "es";
type Copy = { title: string; body: string; learned?: string };

export type Stage = {
  from: number;
  to: number;
  en: Copy;
  es: Copy;
  /** Technologies adopted in this stage, e.g. ["Laravel", "Vue.js"]. */
  tech: string[];
  /** Set to true once Fernando has reviewed and completed the stage. */
  confirmed: boolean;
  /** What we still need to know to finish this stage. */
  needs: string;
};

export const FIRST_YEAR = 2016;
export const LAST_YEAR = 2026;

export const STAGES: Stage[] = [
  {
    from: 2016,
    to: 2017,
    en: {
      title: "From brief to browser",
      body: "Taking what clients asked for and turning it into designed, working website frontends.",
    },
    es: {
      title: "Del requerimiento al navegador",
      body: "Tomar lo que pedían los clientes y convertirlo en frontends de sitios web diseñados y funcionando.",
    },
    tech: [],
    confirmed: false,
    needs: "What did these first years teach you? Which languages and tools did you start with?",
  },
  {
    from: 2018,
    to: 2018,
    en: {
      title: "Rebuilding what already exists",
      body: "Redesigning and rebuilding a job board that people were using, along with every website around it.",
    },
    es: {
      title: "Reconstruir lo que ya existe",
      body: "Rediseñar y reconstruir una bolsa de empleo en uso, junto con todos los sitios a su alrededor.",
    },
    tech: [],
    confirmed: false,
    needs: "What changed in how you approach a rebuild versus a new project? When did backend work (PHP, MySQL) become central?",
  },
  {
    from: 2019,
    to: 2019,
    en: {
      title: "Designing for content",
      body: "Designing and developing a digital magazine from inside a marketing team.",
    },
    es: {
      title: "Diseñar para el contenido",
      body: "Diseñar y desarrollar una revista digital desde dentro de un equipo de marketing.",
    },
    tech: [],
    confirmed: false,
    needs: "What did working with editors and marketers change in your UX/UI thinking?",
  },
  {
    from: 2020,
    to: 2020,
    en: {
      title: "Owning the whole thing",
      body: "Leading IT at an agency and taking client sites and apps from design to development as a full-stack developer.",
    },
    es: {
      title: "Hacerme cargo de todo",
      body: "Dirigir TI en una agencia y llevar sitios y aplicaciones de clientes del diseño al desarrollo como desarrollador full-stack.",
    },
    tech: [],
    confirmed: false,
    needs: "What did leading IT teach you about architecture, infrastructure or managing projects? Which frameworks did you adopt here?",
  },
  {
    from: 2021,
    to: 2021,
    en: {
      title: "One brand, two countries",
      body: "Running the institutional websites of a telecom operator in Guatemala and Nicaragua.",
    },
    es: {
      title: "Una marca, dos países",
      body: "Administrar los sitios institucionales de una operadora de telecomunicaciones en Guatemala y Nicaragua.",
    },
    tech: [],
    confirmed: false,
    needs: "What did maintaining sites at that scale teach you (process, performance, security)?",
  },
  {
    from: 2022,
    to: 2026,
    en: {
      title: "Structure at scale",
      body: "Structuring and implementing websites and web apps for a restaurant group across Mesoamérica.",
    },
    es: {
      title: "Estructura a escala",
      body: "Estructurar e implementar sitios y aplicaciones web para un grupo de restaurantes en Mesoamérica.",
    },
    tech: [],
    confirmed: false,
    needs: "How has your technical thinking changed in these years? Which technologies have you incorporated most recently?",
  },
];

export const stageFor = (year: number) =>
  STAGES.findIndex((s) => year >= s.from && year <= s.to);
