import smtplib
from email.message import EmailMessage
import sys
import os

# Usage: python3 send_mail.py <recipient> <attachment_path>
if len(sys.argv) != 3:
    print(f"Usage: {sys.argv[0]} <recipient> <attachment_path>")
    sys.exit(1)

TO_ADDR = sys.argv[1]
ATTACHMENT_PATH = sys.argv[2]

if not os.path.isfile(ATTACHMENT_PATH):
    print(f"Error: File '{ATTACHMENT_PATH}' does not exist.")
    sys.exit(1)

# SMTP server (open relay)
SMTP_SERVER = "192.168.215.140"
SMTP_PORT = 25

# Email details
FROM_ADDR = "jonas@localhost"
SUBJECT = "CTF Mail"
BODY = "Here is your file."

# Build message
msg = EmailMessage()
msg["From"] = FROM_ADDR
msg["To"] = TO_ADDR
msg["Subject"] = SUBJECT
msg.set_content(BODY)

with open(ATTACHMENT_PATH, "rb") as f:
    msg.add_attachment(
        f.read(),
        maintype="application",
        subtype="octet-stream",
        filename=os.path.basename(ATTACHMENT_PATH)
    )

# Send email via open relay (no auth)
with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
    server.send_message(msg)
    print(f"[+] Email with '{ATTACHMENT_PATH}' sent to {TO_ADDR} (no authentication).")
