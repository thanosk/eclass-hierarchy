#!/bin/bash

php draw.php > uni.dot && dot -T png uni.dot > uni.png && eog -c uni.png
