import telebot # type: ignore
import mysql.connector # type: ignore
import random
import string

API_TOKEN = '//' 
bot = telebot.TeleBot(API_TOKEN)
db = mysql.connector.connect(
    host="//",
    user="//",             
    password="//", 
    database="//"   
)
cursor = db.cursor()

def generate_random_id():
    return ''.join(random.choices(string.ascii_uppercase + string.digits, k=10))

@bot.message_handler(commands=['send'])
def send_message_by_id(message):
    command_parts = message.text.split()
    
    if len(command_parts) != 2:
        bot.reply_to(message, "Bitte gib eine Random-ID an. Beispiel: /send ABC123XYZ")
        return

    random_id = command_parts[1]

    cursor.execute("SELECT telegram_id, message FROM messages WHERE random_id = %s AND status = 'pending'", (random_id,))
    result = cursor.fetchone()

    if result:
        telegram_id, msg = result
        bot.send_message(telegram_id, msg)

        cursor.execute("UPDATE messages SET status = 'sent' WHERE random_id = %s", (random_id,))
        db.commit()

        bot.reply_to(message, f"Nachricht mit Random-ID {random_id} wurde gesendet.")
    else:
        bot.reply_to(message, f"Keine Nachricht mit Random-ID {random_id} gefunden oder bereits gesendet.")

@bot.message_handler(commands=['add'])
def add_message(message):
    try:
        command_parts = message.text.split(maxsplit=2)

        if len(command_parts) < 3:
            bot.reply_to(message, "Verwendung: /add <TELEGRAM_ID> <NACHRICHT>")
            return

        telegram_id = int(command_parts[1])
        msg = command_parts[2]
        random_id = generate_random_id()

        cursor.execute(
            "INSERT INTO messages (telegram_id, random_id, message) VALUES (%s, %s, %s)",
            (telegram_id, random_id, msg)
        )
        db.commit()

        bot.reply_to(message, f"Nachricht hinzugefügt mit Random-ID: {random_id}")
    except Exception as e:
        bot.reply_to(message, f"Fehler: {str(e)}")

# 3. Nachricht löschen
@bot.message_handler(commands=['delete'])
def delete_message(message):
    command_parts = message.text.split()

    if len(command_parts) != 2:
        bot.reply_to(message, "Verwendung: /delete <RANDOM_ID>")
        return

    random_id = command_parts[1]

    # Nachricht mit der Random-ID aus der Datenbank löschen
    cursor.execute("DELETE FROM messages WHERE random_id = %s", (random_id,))
    db.commit()

    if cursor.rowcount > 0:
        bot.reply_to(message, f"Nachricht mit Random-ID {random_id} wurde gelöscht.")
    else:
        bot.reply_to(message, f"Keine Nachricht mit Random-ID {random_id} gefunden.")

# 4. Liste aller Nachrichten anzeigen
@bot.message_handler(commands=['list'])
def list_messages(message):
    cursor.execute("SELECT random_id, telegram_id, message, status FROM messages")
    results = cursor.fetchall()

    if results:
        response = "Gespeicherte Nachrichten:\n"
        for random_id, telegram_id, msg, status in results:
            response += f"ID: {random_id}, Telegram-ID: {telegram_id}, Status: {status}, Nachricht: {msg}\n"
        bot.reply_to(message, response)
    else:
        bot.reply_to(message, "Es gibt keine gespeicherten Nachrichten.")

# Bot starten
bot.polling()
