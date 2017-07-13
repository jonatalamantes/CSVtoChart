function hiddenElement(id, status, type)
{
    if (status === "true")
    {
        $("#" + id).css("display", "none");
    }
    else
    {
        if (type === undefined)
        {
            $("#" + id).css("display", "block");
        }
        else
        {
            $("#" + id).css("display", type);
        }        
    }
}

function href(url)
{
    window.location.href = url;
}

function nextPage(state, number)
{
    url = String(window.location);
    
    pageBegin = -1;

    for (var i = 0; i < url.length - 5; i++) 
    {
        if (url.substring(i, i+5) == 'page=')
        {
            pageBegin = i;
            break;
        }
    }

    if (pageBegin == -1) //page 0
    {
        if (state === 'true' || state === 'false')
        {
            if (url.indexOf('?') == -1)
            {
                url = url + "?page=1";
            }
            else
            {
                url = url + "&page=1";
            }
        }
        else
        {
            if (url.indexOf('?') == -1)
            {
                url = url + "?page=" + number;
            }
            else
            {
                url = url + "&page=" + number;
            }   
        }
    }
    else
    {
        pageEnd = -1;

        for (i = pageBegin; i < url.length; i++)
        {
            if (url[i] == '&')
            {
                pageEnd = i;
                break;
            }
        }

        if (pageEnd == -1)
        {
            pageEnd = url.length;
        }

        urlPrev = url.substring(pageBegin, pageEnd);

        if (state == 'true')
        {
            urlNext = "page=" + (parseInt(urlPrev.substring(5)) + 1);
        }
        else if (state == 'set')
        {
            urlNext = "page=" + number;
        }
        else
        {
            urlNext = "page=" + (parseInt(urlPrev.substring(5)) - 1);
        }

        url = url.replace(urlPrev, urlNext);
    }

    window.location.href = url;
}

function inputNumeric(id)
{
    obj =  document.getElementById(id);

    cad = obj.value;

    var letras = '01234567890+-';

    for (var i = 0; i < cad.length; i++) 
    {
        if (letras.indexOf(cad[i]) === -1)
        {
            cad = cad.substring(0, i) + cad.substring(i+1, i+2);
            i--;
        }
    }
    
    obj.value = cad;
}

function inputName(id)
{
    obj =  document.getElementById(id);
    cad = obj.value;

    var letrasM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZÑÁÉÍÓÚŮ .';
    var letrasm = 'abcdefghijklmnopqrstuvwxyzñáéíóúů .';

    while (cad[0] == " ")
    {
        cad = cad.substring(1);
    }

    if (cad.length > 1) //put the upper at begin
    {
        if (letrasM.indexOf(cad[0]) == -1)
        {
            cad = cad.substring(0,1).toUpperCase() + cad.substring(1).toLowerCase();
        }
    }

    for (var i = cad.length -2; i >= 0; i--) //validate the space + upper
    {
        if (cad[i] == " ")
        {
            cad = cad.substring(0, i+1)+cad.substring(i+1, i+2).toUpperCase() + cad.substring(i+2);
        }
    }

    if (cad[cad.length-2] == " ") //begin of name
    {
        if (letrasM.indexOf(cad[cad.length-1]) == -1)
        {
            if (letrasm.indexOf(cad[cad.length-1]) == -1)
            {
                cad = cad.substring(0, cad.length - 1);
            }
            else
            {
                cad = cad.substring(0, cad.length-1) + cad[cad.length-1].toUpperCase();
            }
        }
    }
    else
    {
        if (letrasM.indexOf(cad[cad.length-1]) == -1)
        {
            if (letrasm.indexOf(cad[cad.length-1]) == -1) //middle letter
            {
                cad = cad.substring(0, cad.length - 1);
            }
        }
        else
        {
            cad = cad.substring(0, cad.length - 1) + cad.substring(cad.length-1).toLowerCase();        
        }
    }
    
    if (cad.length === 1)
    {
        cad = cad.toUpperCase();
    }

    obj.value = cad;
}

function inputCharacter(id)
{
    obj =  document.getElementById(id);

    cad = obj.value;

    var letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZÑÁÉÍÓÚŮ abcdefghijklmnopqrstuvwxyzñáéíóúů01234567890#$%&/.,!?¿*+-_@';

    if (letras.indexOf(cad[cad.length - 1]) == -1)
    {
        cad = cad.substring(0, cad.length - 1);
    }
    
    obj.value = cad;
}

function inputUpper(id)
{
    inputCharacter(id);

    obj =  document.getElementById(id);    
    obj.value = obj.value.toUpperCase();
}

function inputLower(id)
{
    inputCharacter(id);

    obj =  document.getElementById(id);    
    obj.value = obj.value.toLowerCase();
}

function inputClock(id)
{
    obj =  document.getElementById(id);

    cad = obj.value;
    var letras = "1234567890";

    cad = cad.substring(0,5);
    finalCad = "";

    for (i = 0; i < 5; i++) 
    {
        if (i > cad.length)
        {
            break;
        }

        if (i == 2)
        {
            if (cad[i] == ":")
            {
                finalCad = finalCad + ":";
            }
        }
        else
        {
            if (letras.indexOf(cad[i]) !== -1)
            {
                finalCad = finalCad + cad[i];   
            }
        }
    }

    obj.value = finalCad;
}

function inputCalendar(obj)
{
    val = $("#" + obj).val();   

    estado = 1;
    num1   = "120";
    num1   = "1230";
    num2   = "1234567890";

    for (i = 0; i < val.length; i++)
    {
        char1 = val[i];

        if (estado == 1)
        {
            if (num1.indexOf(char1) >= 0)
            {
                estado++;
            }
            else
            {
                val = "";
                break;
            }
        }
        else if (estado == 2)
        {
            if (num2.indexOf(char1) >= 0)
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
        else if (estado == 3)
        {
            if (num2.indexOf(char1) >= 0)
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
        else if (estado == 4)
        {
            if (num2.indexOf(char1) >= 0)
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
        else if (estado == 5)
        {
            if (char1 == "-")
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
        else if (estado == 6)
        {
            if (num1.indexOf(char1) >= 0)
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
        else if (estado == 7)
        {
            if (num2.indexOf(char1) >= 0)
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
        else if (estado == 9)
        {
            if (char1 == "-")
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
        else if (estado == 10)
        {
            if (num2.indexOf(char1) >= 0)
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
        else if (estado == 11)
        {
            if (num2.indexOf(char1) >= 0)
            {
                estado++;
            }
            else
            {
                val = val.substring(0, estado-1);
                break;
            }
        }
    }

    val = val.substring(0,10);

    $("#" + obj).val(val);
}

function sha1 (str) {
  //  discuss at: http://locutus.io/php/sha1/
  // original by: Webtoolkit.info (http://www.webtoolkit.info/)
  // improved by: Michael White (http://getsprink.com)
  // improved by: Kevin van Zonneveld (http://kvz.io)
  //    input by: Brett Zamir (http://brett-zamir.me)
  //      note 1: Keep in mind that in accordance with PHP, the whole string is buffered and then
  //      note 1: hashed. If available, we'd recommend using Node's native crypto modules directly
  //      note 1: in a steaming fashion for faster and more efficient hashing
  //   example 1: sha1('Kevin van Zonneveld')
  //   returns 1: '54916d2e62f65b3afa6e192e6a601cdbe5cb5897'
  var hash
  try {
    var crypto = require('crypto')
    var sha1sum = crypto.createHash('sha1')
    sha1sum.update(str)
    hash = sha1sum.digest('hex')
  } catch (e) {
    hash = undefined
  }
  if (hash !== undefined) {
    return hash
  }
  var _rotLeft = function (n, s) {
    var t4 = (n << s) | (n >>> (32 - s))
    return t4
  }
  var _cvtHex = function (val) {
    var str = ''
    var i
    var v
    for (i = 7; i >= 0; i--) {
      v = (val >>> (i * 4)) & 0x0f
      str += v.toString(16)
    }
    return str
  }
  var blockstart
  var i, j
  var W = new Array(80)
  var H0 = 0x67452301
  var H1 = 0xEFCDAB89
  var H2 = 0x98BADCFE
  var H3 = 0x10325476
  var H4 = 0xC3D2E1F0
  var A, B, C, D, E
  var temp
  // utf8_encode
  str = unescape(encodeURIComponent(str))
  var strLen = str.length
  var wordArray = []
  for (i = 0; i < strLen - 3; i += 4) {
    j = str.charCodeAt(i) << 24 |
      str.charCodeAt(i + 1) << 16 |
      str.charCodeAt(i + 2) << 8 |
      str.charCodeAt(i + 3)
    wordArray.push(j)
  }
  switch (strLen % 4) {
    case 0:
      i = 0x080000000
      break
    case 1:
      i = str.charCodeAt(strLen - 1) << 24 | 0x0800000
      break
    case 2:
      i = str.charCodeAt(strLen - 2) << 24 | str.charCodeAt(strLen - 1) << 16 | 0x08000
      break
    case 3:
      i = str.charCodeAt(strLen - 3) << 24 |
        str.charCodeAt(strLen - 2) << 16 |
        str.charCodeAt(strLen - 1) <<
      8 | 0x80
      break
  }
  wordArray.push(i)
  while ((wordArray.length % 16) !== 14) {
    wordArray.push(0)
  }
  wordArray.push(strLen >>> 29)
  wordArray.push((strLen << 3) & 0x0ffffffff)
  for (blockstart = 0; blockstart < wordArray.length; blockstart += 16) {
    for (i = 0; i < 16; i++) {
      W[i] = wordArray[blockstart + i]
    }
    for (i = 16; i <= 79; i++) {
      W[i] = _rotLeft(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1)
    }
    A = H0
    B = H1
    C = H2
    D = H3
    E = H4
    for (i = 0; i <= 19; i++) {
      temp = (_rotLeft(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff
      E = D
      D = C
      C = _rotLeft(B, 30)
      B = A
      A = temp
    }
    for (i = 20; i <= 39; i++) {
      temp = (_rotLeft(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff
      E = D
      D = C
      C = _rotLeft(B, 30)
      B = A
      A = temp
    }
    for (i = 40; i <= 59; i++) {
      temp = (_rotLeft(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff
      E = D
      D = C
      C = _rotLeft(B, 30)
      B = A
      A = temp
    }
    for (i = 60; i <= 79; i++) {
      temp = (_rotLeft(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff
      E = D
      D = C
      C = _rotLeft(B, 30)
      B = A
      A = temp
    }
    H0 = (H0 + A) & 0x0ffffffff
    H1 = (H1 + B) & 0x0ffffffff
    H2 = (H2 + C) & 0x0ffffffff
    H3 = (H3 + D) & 0x0ffffffff
    H4 = (H4 + E) & 0x0ffffffff
  }
  temp = _cvtHex(H0) + _cvtHex(H1) + _cvtHex(H2) + _cvtHex(H3) + _cvtHex(H4)
  return temp.toLowerCase()
}