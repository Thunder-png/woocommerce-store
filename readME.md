# WooCommerce Store Project Setup

## Project Overview

This repository contains the development structure for a WooCommerce-based e-commerce website that sells safety net products priced per square meter (m²).

The system includes:

- WordPress + WooCommerce backend
- Custom child theme frontend
- m² price calculation system
- GitHub-based Kanban project management
- Brand kit system
- Legal compliance pages
- Automation hooks

The project is designed for a **single developer/operator workflow**.

---

# Project Folder Structure


woocommerce-store
│
├─ child-theme
│ ├─ assets
│ │ ├─ css
│ │ └─ js
│ │
│ ├─ woocommerce
│ │ ├─ single-product.php
│ │ └─ archive-product.php
│ │
│ └─ functions.php
│
├─ branding
│ ├─ logo
│ ├─ colors
│ ├─ typography
│ ├─ icons
│ └─ packaging
│
├─ docs
│ ├─ brand-kit.md
│ ├─ product-model.md
│ ├─ site-architecture.md
│ └─ automation-plan.md
│
├─ automation
│ └─ n8n-workflows
│
└─ PROJECT_SETUP.md


---

# WooCommerce Site Architecture


Home
├─ Balcony Safety Net
├─ Stair Safety Net
├─ Industrial Net
├─ Products
│ ├─ Product A
│ ├─ Product B
│
├─ About Us
├─ FAQ
├─ Contact


Footer:


Privacy Policy
KVKK Policy
Distance Sales Agreement
Return Policy
Payment and Delivery
Cookie Policy


---

# Product Model

Products are priced per square meter.

Product fields:


product
├ price_per_m2
├ minimum_size
├ maximum_size
├ rope_type
└ mesh_size


---

# m² Price Calculation Logic


area = width × height
price = area × price_per_m2
vat = price × 0.20
total = price + vat


---

# Frontend Calculation Flow


user enters width
user enters height
↓
calculate area
↓
calculate price
↓
calculate VAT
↓
display total price


---

# Example JavaScript Logic

```javascript
const widthInput = document.getElementById("width")
const heightInput = document.getElementById("height")
const priceDisplay = document.getElementById("price")

const pricePerM2 = 100

function calculate() {

    const width = Number(widthInput.value)
    const height = Number(heightInput.value)

    const area = width * height

    const price = area * pricePerM2

    const vat = price * 0.20

    const total = price + vat

    priceDisplay.innerText = total + " TL"

}

widthInput.addEventListener("input", calculate)
heightInput.addEventListener("input", calculate)
WooCommerce Development Workflow

Development uses a child theme.

Structure:

themes/
 parent-theme
 child-theme

Child theme structure:

child-theme
 ├ assets
 │   ├ css
 │   └ js
 ├ woocommerce
 │   ├ single-product.php
 │   └ archive-product.php
 └ functions.php
Template Override

WooCommerce templates will be overridden using:

child-theme/woocommerce/single-product.php
child-theme/woocommerce/archive-product.php
GitHub Workflow

Each feature is managed with Issues.

Workflow:

create issue
↓
create branch
↓
develop feature
↓
commit
↓
merge
↓
close issue

Branch naming:

feature/m2-calculator
feature/product-layout
fix/checkout-error
GitHub Project Kanban Columns
Inbox
Backlog
Research
Branding
Design
Development
Content
Legal
Testing
Ready
Live
Maintenance
Labels
frontend
backend
woocommerce
design
branding
seo
content
legal
automation
bug
enhancement
research
operations
Milestones
Brand Kit
WooCommerce Setup
Product System
Frontend
Launch
Growth
Legal Pages Required

The following pages must exist on the site:

Privacy Policy

KVKK Policy

Distance Sales Agreement

Return and Cancellation Policy

Payment and Delivery Policy

Cookie Policy

Checkout must require acceptance of the Distance Sales Agreement.

Brand Kit System

Branding folder contains:

logo
colors
typography
icons
packaging

Brand characteristics:

professional

industrial

safety focused

Automation

Automation workflows will be built with n8n.

Possible automations:

new order → notification
new order → google sheet logging
form submission → CRM entry
Daily Workflow
check inbox
select 3 tasks
work on issues
commit changes
close completed issues
Testing Checklist

Before launch:

cart functionality
checkout flow
payment processing
order email notifications
mobile responsiveness
site speed test
Launch Tasks
Google Analytics setup
Search Console setup
sitemap submission
robots.txt configuration
Goal

The goal of this project is to build a WooCommerce-based store that:

sells safety nets priced per m²

calculates price dynamically

supports secure payments

supports shipping calculation

provides a professional industrial brand experience