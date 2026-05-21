---
name: grill-me
description: 'Quiz yourself on Aegean Barangay System knowledge. Tests understanding of project architecture, patterns, conventions, models, authorization, controllers, testing, and frontend stack. Use to validate project familiarity.'
argument-hint: 'Optional: difficulty level (easy, medium, hard) or topic area'
---

# Grill Me: Aegean Project Knowledge Quiz

## Overview

This skill interactively quizzes you on your knowledge of the Aegean Barangay System Laravel project. It covers architecture, design patterns, conventions, and implementation details across all layers.

## When to Use

- Onboarding new team members
- Verifying your own project comprehension
- Preparing for code reviews on specific areas
- Testing knowledge before starting a feature
- Self-assessment after studying the AGENTS.md guide

## Knowledge Areas Tested

1. **Project Architecture & Stack** — Framework versions, dependencies, structure
2. **Data Models & Relationships** — UUID PKs, SoftDeletes, model connections
3. **Authorization & Access Control** — Gates, roles, permission patterns
4. **Controllers & REST** — RESTful conventions, request validation, responses
5. **Frontend Stack** — Blade, Tailwind, Alpine.js, Vite
6. **Testing Patterns** — Pest, factories, fixtures, test organization
7. **Common Workflows** — DataTables, PDF export, flash messages, soft deletes
8. **Folder Structure & Conventions** — Where things go, naming patterns

## Question Categories

### Quick Challenge (15 min)
- 5 easy questions covering core concepts
- Topics: models, routes, authorization basics

### Deep Dive (30 min)
- 10-15 medium questions across multiple areas
- Topics: controller patterns, testing, relationships, conventions

### Expert Mode (45+ min)
- 20+ hard questions with scenario-based challenges
- Topics: edge cases, advanced patterns, integration scenarios

## Sample Questions

**Easy:**
- What is the primary key type used in Aegean models?
- Name 3 core models in the Aegean domain
- What testing framework does the project use?

**Medium:**
- How would you implement authorization for a new resource? (Describe the Gate pattern)
- What eager-loading problem do we prevent with `with()`? (N+1 queries)
- Where are authorization rules defined, and why not use Policies?

**Hard:**
- Design a feature where a Resident can belong to multiple Committees. Show migrations, models, and controller methods.
- A soft-deleted Official should not appear in Committee lists. Write the query and explain soft delete handling.
- Implement a DataTable endpoint with server-side rendering, authorization checks, and custom columns.

## How to Use

1. **Start the quiz**: Type `/grill-me` in chat and select difficulty level
2. **Answer each question**: Provide your best answer (open-ended or multiple-choice)
3. **Get feedback**: Agent explains the correct answer and clarifies concepts
4. **Track progress**: Questions build in complexity; later ones reference earlier ones
5. **Review gaps**: Agent summarizes knowledge gaps and suggests deeper study areas

## Score Interpretation

- **80%+**: Solid project knowledge; ready to contribute independently
- **60-80%**: Good foundation; review flagged areas before complex features
- **40-60%**: Developing understanding; pair with experienced team member
- **<40%**: Study AGENTS.md and project structure more thoroughly

## Resources Referenced

- [AGENTS.md](../../AGENTS.md) — Comprehensive project guide with all patterns and conventions
- [Project Models](../../app/Models/) — Eloquent model definitions with relationships
- [Controllers](../../app/Http/Controllers/) — RESTful controller implementations
- [Tests](../../tests/) — Pest test examples and patterns
- [Migrations](../../database/migrations/) — Schema and relationship structures
- [Views](../../resources/views/) — Blade templates and component patterns

## Tips for Success

1. **Read AGENTS.md first** — Contains all answers; use as reference during quiz
2. **Skim the code** — Look at actual controller/model implementations
3. **Take deep dive mode** — Covers more ground than quick mode
4. **Review failures** — Each wrong answer links to relevant documentation
5. **Discuss patterns** — If scoring <60%, discuss with team lead about unclear areas

## Example Prompts to Try

```
/grill-me easy
/grill-me medium controllers
/grill-me hard authorization
/grill-me deep-dive
```
