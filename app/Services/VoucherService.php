<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Exceptions\VoucherException;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use SimpleXMLElement;

class VoucherService
{
    public function getVouchers(
        int $page,    // the description of this problem indicates that the date range must be the only required set of values"
        int $paginate,
        ?string $currency,
        ?string $serial_number,
        ?string $voucher_id,
        string $start_date,
        string $end_date,
    ): LengthAwarePaginator {

        if ($page < 1 || $paginate < 1) {
            throw new InvalidArgumentException('Both $page and $paginate must be greater than 0.', 400);
        }

        $query = Voucher::with(['lines', 'user'])
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$start_date, $end_date]);

        if ($currency) {
            $query->where('currency', $currency);
        }

        if ($serial_number) {
            $query->where('serial_number', $serial_number);
        }

        if ($voucher_id) {
            $query->where('id', $voucher_id);
        }

        return $query->paginate($paginate, ['*'], 'page', $page);
    }
    public function getTotalAmount(): array
    {
        $rawQueryResult = Voucher::select('currency', DB::raw('SUM(total_amount) as total')) // sum total amount of each currency
            ->where('user_id', auth()->id())
            ->groupBy('currency')
            ->get();

        $totalAmount = [
            'USD' => $rawQueryResult->firstWhere('currency', 'USD')->total ?? 0,
            'PEN' => $rawQueryResult->firstWhere('currency', 'PEN')->total ?? 0,
        ];
        return $totalAmount;
    }

    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $vouchers = [];
        foreach ($xmlContents as $xmlContent) {
            $vouchers[] = $this->storeVoucherFromXmlContent($xmlContent, $user);
        }

        VouchersCreated::dispatch($vouchers, $user);
        return $vouchers;
    }

    public function storeVoucherFromXmlContent(string $xmlContent, User $user): Voucher
    {
        $xml = new SimpleXMLElement($xmlContent);

        $issuerName = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
        $issuerDocumentType = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $issuerDocumentNumber = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $receiverName = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
        $receiverDocumentType = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $receiverDocumentNumber = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $totalAmount = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];
        $serialNumber = (string) $xml->xpath('//cbc:ID')[0];
        $currency = (string)    $xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount/@currencyID')[0];
        $voucherType = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];
        $voucher = new Voucher([
            'user_id' => $user->getAuthIdentifier(),
            'issuer_name' => $issuerName,
            'issuer_document_type' => $issuerDocumentType,
            'issuer_document_number' => $issuerDocumentNumber,
            'receiver_name' => $receiverName,
            'receiver_document_type' => $receiverDocumentType,
            'receiver_document_number' => $receiverDocumentNumber,
            'total_amount' => $totalAmount,
            'currency'  => $currency,
            'serial_number' => $serialNumber,
            'voucher_type' => $voucherType,
            'xml_content' => $xmlContent,

        ]);
        $voucher->save();

        foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
            $name = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0];
            $quantity = (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0];
            $unitPrice = (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

            $voucherLine = new VoucherLine([
                'name' => $name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'voucher_id' => $voucher->id,
            ]);

            $voucherLine->save();
        }
        return $voucher;
    }

    public function deleteVoucher($voucher_id)
    {
        $voucher = Voucher::where('id', $voucher_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$voucher) {
            throw new VoucherException('El comprobante no existe o no tiene permisos para eliminarlo.', 403);
        }
        $voucher->delete();
    }
}
