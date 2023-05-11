# secret
PHP site to facilitate secret sharing in a very secure manner. There are many tools already that do this but I wanted to do my own for the learning experience. To deploy your own instance do the following:
- Clone this repository onto your apache web server
- Update ExtVars.php with your DB credentials
- Run CreateDB.sql on your MySQL or MariaDB database server, ***AFTER*** you change the passwords in that file
- Update the DeleteExpired.sh with the password for the account, then schedule to run every night.

Following is a feature list:
- Form where you fill in text you want to share securely, this could be username and password, API keys, or some other secrets.
- There will be a date field which specifies the expiration date of the message, defaults 7 days but can be adjusted between 1 and 14 days.
- On submit
  - a cryptographically secure long (at least 32 character) key is generate
  - content of the text field is encrypted with the key
  - The encrypted string is saved to a database table along with a GUID
  - User is provided with ID and PWD along with URL that is composed with the GUID and the key
- If the fetch page is called without ID or PWD, then the user is presented with an empty form prompting them for the GUID and the key.
- When the URL is used the key and GUID are parsed out of the URL and placed in the appropriate fields of the fetch form
- Then when the user presses the submit buttom
  - The string associated with that GUID is retrieved from the database, base64 decode and decrypted with the key
  - The decrypted string is shown on the screen and the record deleted from the database

If there are any questions or problems please reach out, I'd love to help you out. support@supergeek.us
