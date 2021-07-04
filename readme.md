# MilliPixels-firebase-chat-API
Laravel - Aspire Loan App

# Composer Command
- composer install


# Migration Command
- php artisan migrate


# Passport Create Encrption Keys
- php artisan passport:install



# API Flow:
- Signup: with name, phone, password
- Login: User Authenticate with customer_id/Password to generate access token.
- Loan Apply: user will aplly loan with only amount/loan_term (loan terms in week)- Aprroved procees in this API. on every loan application approved is automated done. 
- Pay Emi: User will pay emi only with loan_id and amount, no need to check Emi date and EMI-id 


# API: Postman Collection link with Example
- File in attached in this repo on root - Aspire-loan-app.postman_collection.json

# NOTE:
- My system configuration is not updated with new version and I have no much time to update.
- I have PHP version 5.6.40 and MySql 5.6 -  So please take care of it
- Also, I have try to use PHP unittest cases, but its not worked with this version and Also Passport taken is also depricated.
- Hope you consider the business logic of this assignment :pray:

