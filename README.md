# secret
PHP site to facilitate secret sharing in a very secure manner. There are many tools already that do this but I wanted to do my own for the learning experience.
The Feature I plan to implement with this:
- Form where you fill in text you want to share securely, this could be username and password, API keys, or some other secrets.
- There will be a date field which specifies the expiration date of the message, defaults 7 days but can be adjusted between 1 and 14 days. 
- On submit 
  - a cryptographically secure long (at least 32 character) key is generate
  - content of the text field is encrypted with the key
  - The encrypted string is saved to a database table along with a GUID 
  - URL is composed with the GUID and the key
- When the URL is used
  - the key and GUID are parsed out of the URL
  - The string associated with that GUID is retrieved from the database, base64 decode and decrypted with the key
  - The decrypted string is shown on the screen and the record deleted from the database
