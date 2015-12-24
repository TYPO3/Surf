<?php
namespace TYPO3\Surf\Helper;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

/**
 * Ansi definitions for colored output
 */
class Ansi {

	const FG_BLACK = "\033[0;30m";
	const FG_WHITE = "\033[1;37m";
	const FG_GRAY = "\033[0;37m";
	const FG_BLUE = "\033[0;34m";
	const FG_CYAN = "\033[0;36m";
	const FG_YELLOW = "\033[1;33m";
	const FG_RED = "\033[0;31m";
	const FG_GREEN = "\033[0;32m";

	const BG_CYAN = "\033[46m";
	const BG_GREEN = "\033[42m";
	const BG_RED = "\033[41m";
	const BG_YELLOW = "\033[43m";
	const BG_WHITE = "\033[47m";

	const END = "\033[0m";

}
?>