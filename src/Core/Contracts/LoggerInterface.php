<?php
/**
 * Dit is de PSR-3 interface voor loggers.
 *
 * De wereldwijde PHP-standaard voor loggers is vastgelegd in PSR-3.
 * Grote systemen zoals Monolog, maar ook de interne log-engines
 * van Laravel en Symfony, implementeren dit contract.
 *
 * In de absolute basis ziet die interface (platgeslagen zonder de
 * uitgebreide PHPdoc-commentaren) er zo uit.
 *
 * Hier is het nog niet van belang en is het alleen ter referentie opgenomen.
 *
 * Jouw handige debug-snippet maakt gebruik van één centrale functie log( array $data )
 * waarin je direct de complete context (filter, method, $var) in één array-brij
 * naar binnen schiet. Als je de PSR-3 interface zou implementeren,
 * wordt je gedwongen om al die data om te schrijven naar losse strings en
 * context-arrays per log-niveau.
 *
 * Dat is voor jouw snelle workflow pure overhead.
 * Mocht je ooit willen overstappen op een zware syslog-server,
 * dan kun je je eigen Logger altijd nog deze interface geven.
 */
namespace DeWittePrins\Core\Contracts;

interface LoggerInterface {
	// De RFC 5424 Log-niveaus (Van kritiek naar detail)
	public function emergency( string $message, array $context = array() ): void;
	public function alert( string $message, array $context = array() ): void;
	public function critical( string $message, array $context = array() ): void;
	public function error( string $message, array $context = array() ): void;
	public function warning( string $message, array $context = array() ): void;
	public function notice( string $message, array $context = array() ): void;
	public function info( string $message, array $context = array() ): void;
	public function debug( string $message, array $context = array() ): void;

	/**
	 * De centrale methode waar alle bovenstaande functies naartoe sluizen.
	 * Hier geef je het niveau dynamisch mee als string (bijv. 'error' of 'debug').
	 */
	public function log( string $level, string $message, array $context = array() ): void;
}
