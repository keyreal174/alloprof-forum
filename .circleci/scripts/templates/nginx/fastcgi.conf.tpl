fastcgi_param  QUERY_STRING         $query_string;
fastcgi_param  REQUEST_METHOD       $request_method;
fastcgi_param  CONTENT_TYPE         $content_type;
fastcgi_param  CONTENT_LENGTH       $content_length;

fastcgi_param  SCRIPT_NAME          $fastcgi_script_name;
fastcgi_param  REQUEST_URI          $request_uri;
fastcgi_param  DOCUMENT_URI         $document_uri;
fastcgi_param  DOCUMENT_ROOT        $document_root;
fastcgi_param  SERVER_PROTOCOL      $server_protocol;
fastcgi_param  HTTPS                $https if_not_empty;

fastcgi_param  GATEWAY_INTERFACE    CGI/1.1;
fastcgi_param  SERVER_SOFTWARE      nginx/$nginx_version;

fastcgi_param  REMOTE_ADDR          $remote_addr;
fastcgi_param  REMOTE_PORT          $remote_port;
fastcgi_param  SERVER_ADDR          $server_addr;
fastcgi_param  SERVER_PORT          $server_port;
fastcgi_param  SERVER_NAME          $server_name;


# PHP only, required if PHP was built with --enable-force-cgi-redirect
fastcgi_param  REDIRECT_STATUS      200;

fastcgi_split_path_info             "(^.+?\.php)(.*)?$";
fastcgi_param  PATH_INFO            $fastcgi_path_info;
# fastcgi_param  SCRIPT_FILENAME      $document_root$fastcgi_script_name;

fastcgi_intercept_errors            on;
fastcgi_ignore_client_abort         off;
fastcgi_connect_timeout             60;
fastcgi_send_timeout                180;
fastcgi_read_timeout                180;
fastcgi_buffer_size                 128k;
fastcgi_buffers                     4 256k;
fastcgi_busy_buffers_size           256k;
fastcgi_temp_file_write_size        256k;
