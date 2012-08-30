E_NOFILE=66
###########


upload_sql() {
	if [ -f "$1" ]
	then
		FILE_NAME=$1
	else
		echo "File \"$1\" does not exist."
		exit $E_NOFILE
	fi

	MY_CLI="$MY_BIN -h $MY_HOST -u $MY_USER -p$MY_PASS"

	$MY_CLI < $FILE_NAME
}

upload_data() {
	if [ -f "$1" ]
	then
		FILE_NAME=$1
	else
		echo "File \"$1\" does not exist."
		exit $E_NOFILE
	fi

	MY_CLI="$MY_BIN -h $MY_HOST -u $MY_USER -p$MY_PASS"

	$MY_CLI $2 < $FILE_NAME
}
