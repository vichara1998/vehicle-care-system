
# Vehicle Care System

A web-based platform designed to simplify vehicle maintenance for vehicle owners in Sri Lanka. The system integrates garage services, spare parts sales, and customer support into a single, user-friendly solution.

## Project structure

PHP entry points are grouped by feature, and the root `index.php` forwards visitors to the login page. The shared URL helper keeps asset and route links working when the project is installed in a subdirectory. Front-end assets are grouped by type:

```text
auth/                   Login, registration, and logout
pages/                  Main site pages
cart/                   Cart and checkout pages
orders/                 Order history and confirmation pages
actions/
  cart/                 Cart request handlers
  orders/               Order submission handler
app/                    Shared application bootstrap
assets/
  css/                  Stylesheets
  js/                   JavaScript
  images/
    backgrounds/        Page background images
    banners/            Page banner images
    categories/         Category images
    products/           Spare-part images
    social/             Social media icons
```

## Acknowledgements

 - [Awesome Readme Templates](https://awesomeopensource.com/project/elangosundar/awesome-README-templates)
 - [Awesome README](https://github.com/matiassingers/awesome-readme)
 - [How to write a Good readme](https://bulldogjob.com/news/449-how-to-write-a-good-readme-for-your-github-project)


## API Reference

#### Get all garages

```http
  GET /api/garages
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `api_key` | `string` | **Required**. Your API key |



#### Get garage by ID

```http
  GET /api/garages/${id}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id` | `string` | **Required**. ID of the garage to fetch |

#### Purchase Spare Parts

```http
 POST /api/spare-parts
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `part_id` | `string` | **Required**. Spare part ID |
| `quantity` | `int` | **Required**. Quantity |
| `user_details` | `object` | **Required**. User info |






## Appendix

#### Features

- Garage Services: Find and connect with trusted local garages using Google Maps.
- Spare Parts Store: Purchase genuine, high-quality parts from verified suppliers.
- Customer Support: Access seamless support for inquiries and purchases.

#### Technology Stack

- Frontend: HTML, CSS, JavaScript, VueJS
- Backend: MySQL Database, PHP (CodeIgniter Framework)
- Additional Tools: Google Maps API for location services


## Authors

- [@E.M.V.T.Bandara](https://www.https://https://github.com/vichara1998)
- [@T.K.R.Peiris](https://www.https://github.com/KavindaPeiris)
- [@H.M.D.T.Karunathilaka](https://www.https://https://github.com/DTKarunathilaka)


## Badges


[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

## Color Reference

| Color             | Hex                                                                |
| ----------------- | ------------------------------------------------------------------ |
| Hearder Text | ![#333](https://via.placeholder.com/10/0a192f?text=+) #333 |
| Primary Button | ![#007bff](https://via.placeholder.com/10/f8f8f8?text=+) #007bff |


## Contributing

Contributions are always welcome!

See `contributing.md` for ways to get started.

Please adhere to this project's `code of conduct`.


## Demo

Explore the demo [here](https://github.com/vehicleCareSE/VCS_website_group_project).
