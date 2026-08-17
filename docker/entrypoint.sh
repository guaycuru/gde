#!/bin/sh
# Gera /etc/msmtprc em runtime a partir de SMTP_HOST/SMTP_PORT (env, vindas
# do configmap no repo gde-k8s). O hostname do relay não é hardcoded aqui
# de propósito: este repo (gde42) é espelhado publicamente no GitHub, e o
# gde-k8s (privado) é onde os demais hostnames de infra (DB_HOST etc.) já
# ficam. Se SMTP_HOST não estiver setada, pula a geração — mail() volta a
# falhar como antes, sem derrubar o container.
set -e

if [ -n "$SMTP_HOST" ]; then
	cat > /etc/msmtprc <<-EOF
	defaults
	auth        off
	tls         off
	logfile     /var/log/msmtp.log

	account     default
	host        ${SMTP_HOST}
	port        ${SMTP_PORT:-25}
	from        ${MAIL_FROM:-gde@guaycuru.net}
	EOF
	chmod 644 /etc/msmtprc
fi

exec docker-php-entrypoint "$@"
