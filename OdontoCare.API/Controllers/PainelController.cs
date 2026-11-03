using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Security;
using OdontoCare.Application.Services;
namespace OdontoCare.API.Controllers;
[ApiController, Authorize(Roles = Roles.Todos)]
public sealed class PainelController(FinanceiroService service) : ControllerBase
{
    [HttpGet("api/painel")]
    public async Task<IActionResult> Painel()
    {
        int? usuarioId=User.IsInRole(Roles.Dentista) ? int.Parse(User.FindFirst("sub")!.Value) : null;
        var hoje=TimeZoneInfo.ConvertTimeBySystemTimeZoneId(DateTime.UtcNow,"America/Sao_Paulo").Date;
        return Ok(ResultWrapper<PainelDto>.Ok(await service.PainelAsync(usuarioId,hoje)));
    }
    [HttpGet("api/pagamentos/{id:int}/recibo")]
    public async Task<IActionResult> Recibo(int id) => await service.ReciboAsync(id) is { } recibo
        ? Ok(ResultWrapper<ReciboDto>.Ok(recibo)) : NotFound(ResultWrapper<object>.NotFound("Pagamento não encontrado."));
}
