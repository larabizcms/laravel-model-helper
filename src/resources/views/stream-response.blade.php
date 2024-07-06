<!DOCTYPE>
<html>
<head>
    <title>Stream ajax test</title>
    <meta charset="UTF-8"/>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
</head>
<body>
<pre id="response"></pre>

<script type="text/javascript">
    var last_response_len = false;
    $.ajax(
        '{{ route('admin.export.processed', [$progress->key()]) }}',
        {
            xhrFields: {
                onprogress: function (e) {
                    let this_response, response = e.currentTarget.response;
                    if (last_response_len === false) {
                        this_response = response;
                        last_response_len = response.length;
                    } else {
                        this_response = response.substring(last_response_len);
                        last_response_len = response.length;
                    }
                    console.log(this_response);
                    $('#response').html(this_response);

                    const res = JSON.parse(this_response);
                    if (res.done) {
                        $('#response').html('<a href="' + res.fileUrl + '">Download File</a>');
                    }
                }
            }
        })
        .done(function (data) {
            console.log('Complete response = ' + data);
        })
        .fail(function (data) {
            console.log('Error: ', data);
        });
    console.log('Request Sent');
</script>
</body>
</html>
