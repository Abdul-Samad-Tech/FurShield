# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Pet owners are the primary users. Their job is to keep one record for each pet, book care, shop for essentials, and pursue adoption.

Veterinarians and animal shelters are supporting users. They work from the owner's pet record: vets handle appointments and treatments, shelters list animals and respond to adoption interest. Administrators manage role privileges. They are not the primary audience.

## Product Purpose

FurShield is the Aptech FurShield SRS pet-care platform. It exists so an owner can hold a pet's identity, health, appointments, shopping, and adoption path in one place, and so a vet or shelter can act on that same record.

Success means an owner can complete those jobs without leaving the product, and a vet or shelter can respond from the record the owner already keeps.

## Positioning

The owner holds the pet record, and veterinarians and shelters work from it. A shop, a clinic calendar, or an adoption board alone could not make that claim.

## Operating Context

Owners register, keep multiple pet profiles, upload health and insurance documents, book and cancel vet appointments, browse care articles, and place orders from a cart. Vets approve, reschedule, or complete appointments and log treatments. Shelters publish adoptable pets, keep care logs, and answer adopter interest. An admin uses a role-by-feature privilege matrix.

Local use is a browser against the Laravel app. Seeded demo accounts exist for each role.

## Capabilities and Constraints

The current product includes owner, vet, shelter, and admin roles; pet profiles and health timelines; vet appointments; a product catalog and cart; care articles; adoption listings and interest; in-app notifications; an about/contact page; and an optional owner assistant that stays off unless a server-side key is set.

Binding SRS limits, confirmed for future work:

- No payment gateway.
- No physical product delivery.
- No veterinarian credential checks.
- No general chatbot. The optional assistant must not diagnose, give medication doses, or expose its API key.

The geographic market is undecided. The contact page names a Karachi address; that has not been confirmed as a market commitment or as disposable sample copy.

## Brand Commitments

The product name is FurShield. The shipped line is "Every Paw/Wing Deserves a Shield of Love".

## Evidence on Hand

README and ReadMe.txt record the SRS scope and these demo accounts, all with the password `password`: admin@furshield.test, owner@furshield.test, vet@furshield.test, shelter@furshield.test. Seeded pets, products, and articles are sample data.

No verified customer quotes, case studies, or press were confirmed. Future work must not present invented ones as real.

## Product Principles

- Design for the owner's job first. Vet, shelter, and admin tools serve the record the owner holds.
- Stay inside the confirmed SRS limits. Do not add payments, delivery, license checks, or a chatbot.
- One pet record is the shared object. Do not split care, shopping, and adoption into unrelated products.
- Sample seed data is not evidence. Do not promote it as customer proof.
