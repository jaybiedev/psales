    code = array("<reset>", "<bold>", "</bold>", "<normal>", "<small1>", "<small2>", "<small3>", "<header>", "</header>", "<break>", "<eject>", "<drawer>", "<cutter>", "<cutter1>", "<cutterm>", "<tall>", "</tall>", "<wide>", "</wide>")
    value = array(Chr(27) + "@", Chr(27) + "E", Chr(27) + "F", Chr(27) + "F", Chr(27) + "M", Chr(27) + "p", Chr(15), Chr(27) + "W1", Chr(27) + "W0", vbCrLf, Chr(12), Chr(27) + Chr(112) + Chr(0) + Chr(48), Chr(27) + "d0", Chr(27) + "d1", Chr(27) + "m", Chr(27) + "!" + Chr(16), Chr(27) + "!" + Chr(15), Chr(27) + "!" + Chr(32), Chr(27) + "!" + Chr(31))


///
drop table promo_header drop constraint promo_header_date_from_key restricth
