<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $eventName }}</title>
    <style>
        p {
            margin: 0;
        }

        .ticket-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
            width: 100%;
            text-align: left;
            position: relative;
        }

        .ticket-wrapper {
            border: 1px solid #00bcff;
            background: #dff2fe;
            min-height: 193px;
            border-radius: 0.5rem;
            padding: 1rem 2.5rem;
        }

        .ticket-item {
            display: flex;
            flex-direction: row;
            gap: 2.5rem;
            position: relative;
        }

        .ticket-item:before {
            content: "";
            position: absolute;
            width: 25px;
            height: 50px;
            left: -41px;
            top: 40%;
            border-bottom-right-radius: 75px;
            border-top-right-radius: 75px;
            border: 1px solid #00bcff;
            border-left: 0;
            background-color: #fff;
        }

        .ticket-item:after {
            content: "";
            position: absolute;
            width: 25px;
            height: 50px;
            right: -41px;
            top: 40%;
            border-bottom-left-radius: 75px;
            border-top-left-radius: 75px;
            border: 1px solid #00bcff;
            border-right: 0;
            background-color: #fff;
        }

        .devider {
            width: 2px;
            height: auto;
            min-height: 190px;
            background-image: linear-gradient(to bottom, #a3a5a6 50%, transparent 50%);
            background-position: left;
            background-size: 100% 8px;
            background-repeat: repeat-y;
            position: relative;
        }

        .devider::before {
            content: "";
            position: absolute;
            width: 25px;
            height: 50px;
            left: -11.5px;
            top: -30px;
            border-bottom-right-radius: 75px;
            border-top-right-radius: 75px;
            border: 1px solid #00bcff;
            border-left: 0;
            background-color: #fff;
            transform: rotate(90deg);
        }

        .devider::after {
            content: "";
            position: absolute;
            width: 25px;
            height: 50px;
            left: -11.5px;
            bottom: -18%;
            border-bottom-right-radius: 75px;
            border-top-right-radius: 75px;
            border: 1px solid #00bcff;
            border-left: 0;
            background-color: #fff;
            transform: rotate(-90deg);
        }

        .list-detail {
            list-style: inside;
            padding-left: 15px;
            font-size: 14px;
        }
    </style>
</head>

<body style="width:100%; justify-content:center; align-items:center; display:flex; flex-direction: column; margin:0">
    <div style="width:50rem; display:flex; flex-direction:column; margin-block:2.5rem;">
        <div style="position: relative; height:29rem;">
            <img src="{{ $coverImage }}" alt="Cover Tiket"
                style="position: absolute; height: 100%; width: 100%; inset: 0px; color: transparent; border-top-left-radius: 1rem; border-top-right-radius: 1rem;" />
        </div>
        <div
            style="display: flex; flex-direction:column; padding:1.25rem 0.75rem; background-color:white; border:1px solid rgb(169, 163, 163); border-top:0; border-bottom-right-radius:1rem; border-bottom-left-radius:1rem; gap:1rem;">
            <p>
                Jazakallah Khairan Katsiran, <strong>{{ $name }}</strong>!
                <span style="padding-left: 0.25rem; padding-right:0.25rem">
                    {{ $amount > 0 ? 'Pembayaran Anda untuk tiket' : 'Registrasi' }}
                </span>
                <strong>{{ $name }}</strong> telah kami terima dengan sukses.
            </p>
            <p>
                Simpan dan tampilkan e-tiket ini saat hari H atau saat proses check-in.
            </p>
            <div style="display: flex; flex-direction:column; align-items:center; padding-inline:1.25rem">
                <div class="ticket-container">
                    @foreach ($ticketList as $ticket)
                        <div class="ticket-wrapper">
                            <div class="ticket-item">
                                <div style="width:100%">
                                    <p style="font-weight:bold; font-size:1.5rem;">{{ $ticket['title'] }}</p>
                                    <p>{{ $ticket['date'] }}</p>
                                    <div style="padding-top:1.25rem">
                                        <p>Tiket ini sudah termasuk:</p>
                                        <ul class="list-detail">
                                            @foreach ($ticket['description'] as $description)
                                                <li>
                                                    {{ $description }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="devider"></div>
                                <div
                                    style="display:flex; flex-direction:column; padding-top:1rem; align-items: center;">
                                    <img src="{{ $ticket['url'] }}" alt="QR Code" width="150" height="150" />
                                    <p style="font-size:0.75rem">{{ $ticket['qr_code'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <br />
        <br />
        Hormat kami,
        <br />
        <br />
        <br />
        <br />
        <br />
        {{ $eventName }}
    </div>
</body>

</html>
